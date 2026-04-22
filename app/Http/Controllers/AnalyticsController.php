<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\NdviRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{

    public function index(Request $request): View
    {
        $user    = auth()->user();
        $isAdmin = $user->isAdmin();

        $cycleQuery = $isAdmin ? CropCycle::query() : CropCycle::where('user_id', $user->id);

        // Yield prediction trend by crop type
        $yieldByCrop = $cycleQuery->clone()
            ->selectRaw('crop_type, AVG(yield_prediction) as avg_yield, COUNT(*) as count')
            ->whereNotNull('yield_prediction')
            ->groupBy('crop_type')
            ->get();

        // NDVI peak by season
        $ndviBySeaon = $cycleQuery->clone()
            ->selectRaw('season, AVG(ndvi_max) as avg_peak_ndvi, COUNT(*) as count')
            ->whereNotNull('ndvi_max')
            ->groupBy('season')
            ->get();

        // Average growing duration per crop
        $growingDays = $cycleQuery->clone()
            ->selectRaw("crop_type, AVG(julianday(harvest_date) - julianday(sowing_date)) as avg_days")
            ->whereNotNull('harvest_date')
            ->whereNotNull('sowing_date')
            ->groupBy('crop_type')
            ->get();

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
