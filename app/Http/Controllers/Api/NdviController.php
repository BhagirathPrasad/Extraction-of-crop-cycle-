<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CropCycle;
use App\Models\NdviRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NdviController extends Controller
{
    /**
     * GET /api/v1/ndvi/{cropCycle}
     * Returns all NDVI records for a crop cycle.
     */
    public function index(CropCycle $cropCycle): JsonResponse
    {
        if (!auth()->user()->isAdmin() && $cropCycle->user_id !== auth()->id()) {
            abort(403);
        }

        $records = $cropCycle->ndviRecords()
            ->orderBy('observation_date')
            ->get([
                'observation_date', 'ndvi_value', 'evi_value', 'savi_value',
                'lai_value', 'growth_stage', 'temperature', 'rainfall',
                'humidity', 'soil_moisture', 'day_of_year', 'satellite_source',
            ]);

        return response()->json([
            'crop_cycle_id' => $cropCycle->id,
            'count'         => $records->count(),
            'records'       => $records,
            'summary'       => [
                'ndvi_max'  => $records->max('ndvi_value'),
                'ndvi_min'  => $records->min('ndvi_value'),
                'ndvi_mean' => round($records->avg('ndvi_value'), 4),
            ],
        ]);
    }

    /**
     * GET /api/v1/dashboard/stats
     */
    public function dashboardStats(Request $request): JsonResponse
    {
        $user    = auth()->user();
        $isAdmin = $user->isAdmin();

        return response()->json([
            'total_datasets'     => $isAdmin ? \App\Models\Dataset::count() : $user->datasets()->count(),
            'processed_datasets' => $isAdmin ? \App\Models\Dataset::where('status','processed')->count()
                                             : $user->datasets()->where('status','processed')->count(),
            'total_crop_cycles'  => $isAdmin ? CropCycle::count() : $user->cropCycles()->count(),
            'total_ndvi_records' => $isAdmin ? NdviRecord::count()
                                             : NdviRecord::whereHas('cropCycle', fn($q) => $q->where('user_id', $user->id))->count(),
            'active_users'       => $isAdmin ? \App\Models\User::where('is_active', true)->count() : null,
        ]);
    }
}
