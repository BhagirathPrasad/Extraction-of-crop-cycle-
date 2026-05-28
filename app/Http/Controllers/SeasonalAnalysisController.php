<?php

namespace App\Http\Controllers;

use App\Models\CropCycle;
use Illuminate\Http\JsonResponse;

class SeasonalAnalysisController extends Controller
{
    /**
     * Return SOS, EOS, Peak, GDD and smoothed NDVI data for a crop cycle.
     * Used as AJAX endpoint by the crop cycle show page for ApexCharts.
     */
    public function show(CropCycle $cropCycle): JsonResponse
    {
        // Ensure user can only access their own crop cycles (or admin)
        $user = auth()->user();
        if (!$user->isAdmin() && (string) $cropCycle->user_id !== (string) $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ndviRecords = $cropCycle->ndviRecords()->get();

        // Build timeline phase labels
        $phases = [];
        $sowingDate     = $cropCycle->sowing_date?->format('Y-m-d');
        $emergenceDate  = $cropCycle->emergence_date?->format('Y-m-d');
        $tillering      = $cropCycle->tillering_date?->format('Y-m-d');
        $heading        = $cropCycle->heading_date?->format('Y-m-d');
        $peakDate       = $cropCycle->peak_growth_date?->format('Y-m-d');
        $maturityDate   = $cropCycle->maturity_date?->format('Y-m-d');
        $harvestDate    = $cropCycle->harvest_date?->format('Y-m-d');
        $sosDate        = $cropCycle->sos_date;
        $eosDate        = $cropCycle->eos_date;

        if ($sowingDate)    $phases[] = ['date' => $sowingDate,    'label' => 'Sowing',    'color' => '#f59e0b'];
        if ($sosDate)       $phases[] = ['date' => $sosDate,       'label' => 'SOS',       'color' => '#22c55e'];
        if ($emergenceDate) $phases[] = ['date' => $emergenceDate, 'label' => 'Emergence', 'color' => '#84cc16'];
        if ($tillering)     $phases[] = ['date' => $tillering,     'label' => 'Tillering', 'color' => '#10b981'];
        if ($heading)       $phases[] = ['date' => $heading,       'label' => 'Heading',   'color' => '#06b6d4'];
        if ($peakDate)      $phases[] = ['date' => $peakDate,      'label' => 'Peak',      'color' => '#6366f1'];
        if ($maturityDate)  $phases[] = ['date' => $maturityDate,  'label' => 'Maturity',  'color' => '#f97316'];
        if ($eosDate)       $phases[] = ['date' => $eosDate,       'label' => 'EOS',       'color' => '#ef4444'];
        if ($harvestDate)   $phases[] = ['date' => $harvestDate,   'label' => 'Harvest',   'color' => '#dc2626'];

        // Calculate season duration (SOS → EOS or sowing → harvest)
        $seasonDays = null;
        if ($sosDate && $eosDate) {
            $seasonDays = \Carbon\Carbon::parse($sosDate)->diffInDays(\Carbon\Carbon::parse($eosDate));
        } elseif ($sowingDate && $harvestDate) {
            $seasonDays = \Carbon\Carbon::parse($sowingDate)->diffInDays(\Carbon\Carbon::parse($harvestDate));
        }

        // NDVI time series for chart (raw + smoothed)
        $rawSeries      = $ndviRecords->map(fn($r) => ['x' => $r->observation_date, 'y' => (float) $r->ndvi_value])->values();
        $eviSeries      = $ndviRecords->filter(fn($r) => $r->evi_value !== null)->map(fn($r) => ['x' => $r->observation_date, 'y' => (float) $r->evi_value])->values();
        $rainSeries     = $ndviRecords->filter(fn($r) => $r->rainfall !== null)->map(fn($r) => ['x' => $r->observation_date, 'y' => (float) $r->rainfall])->values();
        $tempSeries     = $ndviRecords->filter(fn($r) => $r->temperature !== null)->map(fn($r) => ['x' => $r->observation_date, 'y' => (float) $r->temperature])->values();

        // Smoothed NDVI (stored on crop cycle, fallback to raw)
        $smoothedNdvi   = $cropCycle->smoothed_ndvi ?? null;
        $smoothedSeries = $smoothedNdvi
            ? collect($smoothedNdvi)->map(fn($p) => ['x' => $p['date'], 'y' => $p['ndvi']])->values()
            : $rawSeries;

        return response()->json([
            'crop_cycle_id'  => (string) $cropCycle->id,
            'crop_type'      => $cropCycle->crop_type,
            'sos_date'       => $sosDate,
            'eos_date'       => $eosDate,
            'peak_date'      => $cropCycle->peak_date ?? $peakDate,
            'gdd_total'      => $cropCycle->gdd_total,
            'season_days'    => $seasonDays,
            'phases'         => $phases,
            'series' => [
                'ndvi_raw'      => $rawSeries,
                'ndvi_smoothed' => $smoothedSeries,
                'evi'           => $eviSeries,
                'rainfall'      => $rainSeries,
                'temperature'   => $tempSeries,
            ],
            'yield' => [
                'prediction'       => $cropCycle->yield_prediction,
                'confidence_lower' => $cropCycle->yield_confidence_lower,
                'confidence_upper' => $cropCycle->yield_confidence_upper,
                'unit'             => 'kg/ha',
            ],
        ]);
    }
}
