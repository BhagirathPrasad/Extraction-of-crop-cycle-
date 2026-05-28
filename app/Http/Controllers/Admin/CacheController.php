<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CacheController extends Controller
{
    /** Cache key groups managed by CropsCycle */
    protected array $cacheGroups = [
        'dashboard_stats'        => 'Dashboard Statistics (10 min TTL)',
        'ndvi_chart'             => 'NDVI Chart Data (30 min TTL)',
        'analytics_yield_by_crop'=> 'Analytics: Yield by Crop (1 hr TTL)',
        'analytics_ndvi_by_season' => 'Analytics: NDVI by Season (1 hr TTL)',
        'analytics_growing_days' => 'Analytics: Growing Days (1 hr TTL)',
        'weather'                => 'Weather API Data (1 hr TTL)',
    ];

    public function index(): View
    {
        $groups = collect($this->cacheGroups)->map(function ($label, $prefix) {
            return [
                'prefix'  => $prefix,
                'label'   => $label,
                'cached'  => Cache::has($prefix) || Cache::has($prefix . '_'), // approximate check
            ];
        });

        $cacheDriver = config('cache.default');
        $stats = [
            'driver'         => $cacheDriver,
            'total_groups'   => count($this->cacheGroups),
            'cache_store'    => config('cache.stores.' . $cacheDriver . '.driver', $cacheDriver),
        ];

        return view('admin.cache', compact('groups', 'stats'));
    }

    /** Clear a specific cache group by prefix */
    public function clearGroup(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $prefix = $request->validate(['prefix' => 'required|string'])['prefix'];

        if (!array_key_exists($prefix, $this->cacheGroups)) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Invalid cache group.'], 422)
                : back()->with('error', 'Invalid cache group.');
        }

        // Clear all keys matching this prefix for all users
        // Since file cache doesn't support tags, we flush individual known keys
        $cleared = 0;
        $userIds = \App\Models\User::pluck('_id')->toArray();

        foreach ($userIds as $userId) {
            $key = $prefix . '_' . $userId;
            if (Cache::has($key)) {
                Cache::forget($key);
                $cleared++;
            }
        }

        // Also try without user suffix (e.g. weather keys use lat/lng)
        if (Cache::has($prefix)) {
            Cache::forget($prefix);
            $cleared++;
        }

        $message = "Cleared {$cleared} cache entries for: {$this->cacheGroups[$prefix]}";

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $message, 'cleared' => $cleared])
            : back()->with('success', $message);
    }

    /** Flush all application cache */
    public function clearAll(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Cache::flush();

        \App\Models\ActivityLog::log('cache_cleared', 'Admin flushed all application cache.');

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => 'All application cache has been cleared.'])
            : back()->with('success', 'All application cache cleared successfully.');
    }
}
