<?php

namespace App\Http\Controllers;

use App\Models\CropCycle;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display the search results page.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->isAdmin()
            ? CropCycle::query()
            : CropCycle::where('user_id', auth()->id());

        // Text Search (Matches Crop Type, Variety, Region/Location, Notes)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('crop_type', 'like', "%{$q}%")
                    ->orWhere('variety', 'like', "%{$q}%")
                    ->orWhere('region', 'like', "%{$q}%")
                    ->orWhere('season', 'like', "%{$q}%")
                    ->orWhere('soil_type', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            });
        }

        // Dropdown/Form Filters
        if ($request->filled('crop_type')) {
            $query->where('crop_type', $request->crop_type);
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        if ($request->filled('soil_type')) {
            $query->where('soil_type', $request->soil_type);
        }

        if ($request->filled('region')) {
            $query->where('region', 'like', "%{$request->region}%");
        }

        if ($request->filled('ndvi_min')) {
            $query->where('ndvi_max', '>=', (float)$request->ndvi_min);
        }

        if ($request->filled('ndvi_max_val')) {
            $query->where('ndvi_max', '<=', (float)$request->ndvi_max_val);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $allowedSorts = ['created_at', 'sowing_date', 'ndvi_max', 'yield_prediction', 'crop_type', 'region'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        // Get count and paginated results
        $results = $query->paginate(10)->withQueryString();

        // Get filter options for UI dropdowns
        $distinctQuery = auth()->user()->isAdmin()
            ? CropCycle::query()
            : CropCycle::where('user_id', auth()->id());

        $cropTypes = (clone $distinctQuery)->distinct()->pluck('crop_type')->filter()->sort()->values();
        $seasons = (clone $distinctQuery)->distinct()->pluck('season')->filter()->sort()->values();
        $soilTypes = (clone $distinctQuery)->distinct()->pluck('soil_type')->filter()->sort()->values();
        $regions = (clone $distinctQuery)->distinct()->pluck('region')->filter()->sort()->values();

        // If AJAX request, return compiled list and pagination html
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('search._results', compact('results'))->render(),
                'pagination' => view('search._pagination', compact('results'))->render(),
                'count' => $results->total()
            ]);
        }

        return view('search.index', compact('results', 'cropTypes', 'seasons', 'soilTypes', 'regions'));
    }

    /**
     * API for autocomplete search suggestions.
     */
    public function suggestions(Request $request)
    {
        $q = $request->input('q', '');

        if (strlen($q) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['suggestions' => []], 401);
        }

        // Define searchable system pages
        $pages = [
            [
                'title' => 'Dashboard',
                'description' => 'Overview of crop cycle parameters, NDVI trends, and predictions',
                'url' => route('dashboard'),
                'icon' => 'grid-1x2-fill',
                'type' => 'page',
            ],
            [
                'title' => 'Analytics & AI',
                'description' => 'Predict crop yield and analyze satellite intelligence data',
                'url' => route('analytics.index'),
                'icon' => 'graph-up-arrow',
                'type' => 'page',
            ],
            [
                'title' => 'Datasets',
                'description' => 'Upload, view, and reprocess multi-temporal datasets',
                'url' => route('datasets.index'),
                'icon' => 'database-fill',
                'type' => 'page',
            ],
            [
                'title' => 'Crop Cycle Analysis',
                'description' => 'Manage extracted crop cycle parameters and NDVI timelines',
                'url' => route('crop-cycles.index'),
                'icon' => 'arrow-repeat',
                'type' => 'page',
            ],
            [
                'title' => 'Search Cycles',
                'description' => 'Perform fast dynamic search and filters on crop cycles',
                'url' => route('search.index'),
                'icon' => 'search',
                'type' => 'page',
            ],
            [
                'title' => 'Reports',
                'description' => 'Export crop intelligence insights to PDF and Excel spreadsheets',
                'url' => route('reports.index'),
                'icon' => 'file-earmark-bar-graph-fill',
                'type' => 'page',
            ],
            [
                'title' => 'Notifications',
                'description' => 'Recent activity, user login alerts, and system notifications',
                'url' => route('notifications.index'),
                'icon' => 'bell-fill',
                'type' => 'page',
            ],
            [
                'title' => 'Settings',
                'description' => 'Configure your personal profile settings and password security',
                'url' => route('settings.profile'),
                'icon' => 'gear-fill',
                'type' => 'page',
            ],
        ];

        // Add admin-only pages if the user has the admin role
        if ($user->isAdmin()) {
            $pages[] = [
                'title' => 'Users',
                'description' => 'Manage user accounts, roles, and status configurations',
                'url' => route('users.index'),
                'icon' => 'people-fill',
                'type' => 'page',
            ];
            $pages[] = [
                'title' => 'Activity Logs',
                'description' => 'View administrative and user audit logs',
                'url' => route('activity-logs.index'),
                'icon' => 'clock-history',
                'type' => 'page',
            ];
        }

        // Action pages
        $pages[] = [
            'title' => 'Toggle Theme (Light / Dark Mode)',
            'description' => 'Switch between light and dark display theme preferences',
            'url' => '#toggle-theme',
            'icon' => 'sun-fill',
            'type' => 'action',
        ];
        $pages[] = [
            'title' => 'Logout',
            'description' => 'Securely end your session and sign out',
            'url' => '#logout',
            'icon' => 'box-arrow-right',
            'type' => 'action',
        ];

        // Filter matched pages
        $matchedPages = collect($pages)->filter(function ($page) use ($q) {
            return str_contains(strtolower($page['title']), strtolower($q)) ||
                   str_contains(strtolower($page['description']), strtolower($q));
        })->map(function ($page) {
            return [
                'title' => $page['title'],
                'subtitle' => $page['description'],
                'url' => $page['url'],
                'icon' => $page['icon'],
                'type' => $page['type']
            ];
        })->values();

        // Query database crop cycles matching the input query
        $cycleQuery = $user->isAdmin()
            ? CropCycle::query()
            : CropCycle::where('user_id', $user->id);

        $matches = $cycleQuery->where(function ($subQuery) use ($q) {
            $subQuery->where('crop_type', 'like', "%{$q}%")
                ->orWhere('variety', 'like', "%{$q}%")
                ->orWhere('region', 'like', "%{$q}%")
                ->orWhere('season', 'like', "%{$q}%")
                ->orWhere('soil_type', 'like', "%{$q}%");
        })
        ->limit(6)
        ->get(['id', 'crop_type', 'variety', 'region', 'season', 'season_year', 'soil_type']);

        $formattedCycles = $matches->map(function ($cycle) {
            $soil = $cycle->soil_type ? ucfirst($cycle->soil_type) : 'N/A';
            return [
                'title' => $cycle->crop_type . ($cycle->variety ? " ({$cycle->variety})" : ""),
                'subtitle' => "{$cycle->region} • {$cycle->season} {$cycle->season_year} • {$soil}",
                'url' => route('crop-cycles.show', $cycle->id),
                'icon' => 'flower1',
                'type' => 'crop_cycle',
            ];
        });

        // Merge matched pages/actions and crop cycles
        $allSuggestions = $matchedPages->merge($formattedCycles);

        return response()->json(['suggestions' => $allSuggestions]);
    }
}
