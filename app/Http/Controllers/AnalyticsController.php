<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\NdviRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AnalyticsController extends Controller
{

    public function index(Request $request): View
    {
        $user    = auth()->user();
        $isAdmin = $user->isAdmin();
        $userId  = $user->id;

        $cycleQuery = $isAdmin ? CropCycle::query() : CropCycle::where('user_id', $user->id);

        // Yield prediction trend by crop type (cached 1 hour)
        $yieldByCrop = Cache::remember('analytics_yield_by_crop_' . $userId, 3600, function () use ($cycleQuery) {
            return $cycleQuery->clone()
                ->whereNotNull('yield_prediction')
                ->get(['crop_type', 'yield_prediction'])
                ->groupBy('crop_type')
                ->map(function($group, $cropType) {
                    return [
                        'crop_type' => $cropType,
                        'avg_yield' => $group->avg('yield_prediction'),
                        'count' => $group->count()
                    ];
                })->values()->toArray();
        });

        // NDVI peak by season (cached 1 hour)
        $ndviBySeaon = Cache::remember('analytics_ndvi_by_season_' . $userId, 3600, function () use ($cycleQuery) {
            return $cycleQuery->clone()
                ->whereNotNull('ndvi_max')
                ->get(['season', 'ndvi_max'])
                ->groupBy('season')
                ->map(function($group, $season) {
                    return [
                        'season' => $season,
                        'avg_peak_ndvi' => $group->avg('ndvi_max'),
                        'count' => $group->count()
                    ];
                })->values()->toArray();
        });

        // Average growing duration per crop (cached 1 hour)
        $growingDays = Cache::remember('analytics_growing_days_' . $userId, 3600, function () use ($cycleQuery) {
            return $cycleQuery->clone()
                ->whereNotNull('harvest_date')
                ->whereNotNull('sowing_date')
                ->get(['crop_type', 'harvest_date', 'sowing_date'])
                ->groupBy('crop_type')
                ->map(function($group, $cropType) {
                    $avgDays = $group->map(function($cycle) {
                        return $cycle->growing_days;
                    })->avg();
                    return [
                        'crop_type' => $cropType,
                        'avg_days' => $avgDays
                    ];
                })->values()->toArray();
        });

        // Irrigation events timeline (from suggestions)
        $recentCycles = $cycleQuery->clone()
            ->with('ndviRecords')
            ->whereNotNull('yield_prediction')
            ->latest()
            ->take(10)
            ->get();

        // Prediction accuracy (where actual yield recorded)
        $accuracyData = $cycleQuery->clone()
            ->whereNotNull('yield_prediction')
            ->whereNotNull('actual_yield')
            ->get(['crop_type', 'yield_prediction', 'actual_yield']);

        $yieldByCrop = collect($yieldByCrop);
        $ndviBySeaon = collect($ndviBySeaon);
        $growingDays = collect($growingDays);

        return view('analytics.index', compact(
            'yieldByCrop', 'ndviBySeaon', 'growingDays', 'recentCycles', 'accuracyData'
        ));
    }

    /** AI-style yield forecast for a given crop + NDVI peak (AJAX) */
    public function predictYield(Request $request)
    {
        $request->validate([
            'crop_type' => 'required|string',
            'ndvi_peak' => 'required|numeric|min:0|max:1',
        ]);

        $cropFactors = [
            'wheat'     => 6200, 'rice' => 5800, 'maize' => 7500,
            'cotton'    => 2500, 'sugarcane' => 65000, 'soybean' => 3200,
        ];
        $factor      = $cropFactors[strtolower($request->crop_type)] ?? 4000;
        $prediction  = round($request->ndvi_peak * $factor, 2);
        $category    = $prediction >= 4000 ? 'high' : ($prediction >= 2000 ? 'medium' : 'low');

        $irrigationAdvice = match (true) {
            $request->ndvi_peak < 0.3  => 'Field may be poorly irrigated. Schedule irrigation immediately.',
            $request->ndvi_peak < 0.55 => 'Moderate growth. Monitor soil moisture and irrigate fortnightly.',
            $request->ndvi_peak < 0.75 => 'Good growth detected. Reduce irrigation frequency.',
            default                    => 'Excellent canopy. Maintain current irrigation schedule.',
        };

        return response()->json([
            'crop_type'         => $request->crop_type,
            'ndvi_peak'         => $request->ndvi_peak,
            'yield_prediction'  => $prediction,
            'yield_unit'        => 'kg/ha',
            'yield_category'    => $category,
            'irrigation_advice' => $irrigationAdvice,
        ]);
    }
}
