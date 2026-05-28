<?php

namespace App\Http\Controllers;

use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\NdviRecord;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // ── Summary stats ─────────────────────────────────────────────────
        $statsQuery = fn($model) => $isAdmin ? $model::query() : $model::where('user_id', $user->id);
        $datasetQuery = $statsQuery(Dataset::class);
        $cropCycleQuery = $statsQuery(CropCycle::class);
        $reportQuery = $statsQuery(Report::class);
        $activeAnalyses = (clone $datasetQuery)->whereIn('status', ['pending', 'processing'])->count();
        $datasetAlerts = (clone $datasetQuery)->where('status', 'failed')->count();
        $notificationsEnabled = Schema::hasTable('notifications');
        $unreadNotifications = $notificationsEnabled
            ? $user->unreadNotifications()->count()
            : 0;

        $userId = $user->id;
        $cacheKey = 'dashboard_stats_' . $userId;

        $stats = Cache::remember($cacheKey, 600, function () use ($user, $isAdmin, $datasetQuery, $cropCycleQuery, $reportQuery, $activeAnalyses, $datasetAlerts, $notificationsEnabled, $unreadNotifications) {
            return [
                'total_datasets'     => (clone $datasetQuery)->count(),
                'processed_datasets' => (clone $datasetQuery)->where('status', 'processed')->count(),
                'active_analyses'    => $activeAnalyses,
                'total_crop_cycles'  => (clone $cropCycleQuery)->count(),
                'total_reports'      => (clone $reportQuery)->count(),
                'active_users'       => $isAdmin ? User::where('is_active', true)->count() : null,
                'alerts_count'       => $datasetAlerts + $unreadNotifications,
                'unread_notifications' => $unreadNotifications,
                'total_ndvi_records' => $isAdmin
                    ? NdviRecord::count()
                    : NdviRecord::whereIn('crop_cycle_id', (clone $cropCycleQuery)->pluck('_id'))->count(),
            ];
        });

        // ── Recent activity ───────────────────────────────────────────────
        $recentDatasets = (clone $datasetQuery)
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        $recentCropCycles = (clone $cropCycleQuery)
            ->with(['dataset', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $recentReports = (clone $reportQuery)
            ->latest()
            ->take(5)
            ->get();

        $recentNotifications = $notificationsEnabled
            ? $user->notifications()->latest()->take(6)->get()
            : collect();

        // ── NDVI Chart Data (last 12 months avg per month) ────────────────
        $ndviChartData = $this->getNdviChartData($user, $isAdmin);

        // ── Crop growth stages distribution ───────────────────────────────
        $growthStageData = $this->getGrowthStageData($cropCycleQuery);

        // ── Crop type distribution ────────────────────────────────────────
        $cropDistribution = (clone $cropCycleQuery)
            ->get(['crop_type'])
            ->groupBy('crop_type')
            ->map->count();

        // ── Yield trend (monthly) ─────────────────────────────────────────
        $yieldRecords = (clone $cropCycleQuery)
            ->whereNotNull('yield_prediction')
            ->get(['created_at', 'yield_prediction']);
            
        $yieldTrend = $yieldRecords->groupBy(function($cycle) {
            return $cycle->created_at->format('Y-m');
        })->map(function($group, $month) {
            return (object) [
                'month' => $month,
                'avg_yield' => $group->avg('yield_prediction')
            ];
        })->sortBy('month')->take(-12)->values();

        // ── Processing status breakdown ───────────────────────────────────
        $processingStatus = (clone $datasetQuery)
            ->get(['status'])
            ->groupBy('status')
            ->map->count();

        // ── Combined recent activity table ────────────────────────────────
        $recentActivities = $this->buildRecentActivities(
            $recentDatasets,
            $recentCropCycles,
            $recentReports,
            $recentNotifications
        );

        return view('dashboard.index', compact(
            'stats', 'recentDatasets', 'recentCropCycles',
            'ndviChartData', 'growthStageData', 'cropDistribution', 'yieldTrend',
            'processingStatus', 'recentActivities', 'recentNotifications'
        ));
    }

    /** Build NDVI chart data: average NDVI per month for last 12 months (cached 30 min) */
    private function getNdviChartData(User $user, bool $isAdmin): array
    {
        $cacheKey = 'ndvi_chart_' . $user->id;

        return Cache::remember($cacheKey, 1800, function () use ($user, $isAdmin) {
            $query = NdviRecord::query();

            if (!$isAdmin) {
                $cropCycleIds = CropCycle::where('user_id', $user->id)->pluck('_id');
                $query->whereIn('crop_cycle_id', $cropCycleIds);
            }

            $records = $query
                ->where('observation_date', '>=', now()->subYears(5)->startOfDay())
                ->get(['observation_date', 'ndvi_value']);

            $rows = $records->groupBy(function($rec) {
                return substr($rec->observation_date, 0, 7);
            })->map(function($group, $month) {
                return (object) [
                    'month'    => $month,
                    'avg_ndvi' => $group->avg('ndvi_value'),
                    'count'    => $group->count()
                ];
            })->sortBy('month')->values();

            return [
                'labels' => $rows->pluck('month')->toArray(),
                'values' => $rows->pluck('avg_ndvi')->map(fn($v) => round((float)$v, 4))->toArray(),
            ];
        });
    }

    private function getGrowthStageData($query): array
    {
        $stageCounts = [
            'Sowing'     => (clone $query)->whereNotNull('sowing_date')->count(),
            'Emergence'  => (clone $query)->whereNotNull('emergence_date')->count(),
            'Tillering'  => (clone $query)->whereNotNull('tillering_date')->count(),
            'Peak Growth'=> (clone $query)->whereNotNull('peak_growth_date')->count(),
            'Maturity'   => (clone $query)->whereNotNull('maturity_date')->count(),
            'Harvest'    => (clone $query)->whereNotNull('harvest_date')->count(),
        ];

        return [
            'labels' => array_keys($stageCounts),
            'values' => array_values($stageCounts),
        ];
    }

    private function buildRecentActivities(
        Collection $datasets,
        Collection $cropCycles,
        Collection $reports,
        Collection $notifications
    ): Collection {
        $datasetItems = $datasets->map(fn(Dataset $dataset) => [
            'type' => 'Dataset uploaded',
            'icon' => 'database-fill',
            'title' => $dataset->name,
            'url' => route('datasets.show', $dataset),
            'meta' => trim(($dataset->crop_type ? ucfirst($dataset->crop_type) . ' · ' : '') . ($dataset->region ?? 'Region not set')),
            'status' => ucfirst($dataset->status),
            'status_class' => $dataset->status_badge_class,
            'timestamp' => $dataset->created_at,
        ]);

        $cycleItems = $cropCycles->map(fn(CropCycle $cycle) => [
            'type' => 'Crop analysis completed',
            'icon' => 'flower1',
            'title' => ucfirst($cycle->crop_type) . ' cycle',
            'url' => route('crop-cycles.show', $cycle),
            'meta' => trim(($cycle->region ?? 'Region not set') . ' · ' . ($cycle->season ?? 'Season')),
            'status' => $cycle->yield_prediction ? number_format((float) $cycle->yield_prediction, 0) . ' kg/ha' : 'Detected',
            'status_class' => $cycle->yield_badge_class,
            'timestamp' => $cycle->created_at,
        ]);

        $reportItems = $reports->map(fn(Report $report) => [
            'type' => 'Report generated',
            'icon' => 'file-earmark-bar-graph-fill',
            'title' => $report->title,
            'url' => route('reports.show', $report),
            'meta' => ucfirst($report->type ?? 'Report'),
            'status' => ucfirst($report->status),
            'status_class' => $report->status_badge_class,
            'timestamp' => $report->created_at,
        ]);

        $notificationItems = $notifications->map(function ($notification) {
            $data = $notification->data;

            return [
                'type' => 'System alert',
                'icon' => ($data['icon'] ?? 'bell') . '-fill',
                'title' => $data['title'] ?? 'Notification',
                'url' => $data['action_url'] ?? route('notifications.index'),
                'meta' => $data['message'] ?? 'Platform update',
                'status' => $notification->read_at ? 'Read' : 'New',
                'status_class' => $notification->read_at ? 'badge-secondary' : 'badge-danger',
                'timestamp' => $notification->created_at,
            ];
        });

        return collect()
            ->merge($datasetItems)
            ->merge($cycleItems)
            ->merge($reportItems)
            ->merge($notificationItems)
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();
    }

    /** API endpoint for dashboard stats (AJAX refresh) */
    public function stats(Request $request)
    {
        return response()->json($this->index($request)->getData());
    }
}
