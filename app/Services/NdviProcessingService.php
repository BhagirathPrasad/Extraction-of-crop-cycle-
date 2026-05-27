<?php

namespace App\Services;

use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\NdviRecord;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;

/**
 * NdviProcessingService
 *
 * Handles CSV parsing, NDVI calculation, and crop cycle parameter extraction.
 * For GeoTIFF files, a simulated NDVI is generated from random plausible values.
 */
class NdviProcessingService
{
    // NDVI thresholds for stage detection
    const NDVI_SOWING_THRESHOLD    = 0.15;
    const NDVI_EMERGENCE_THRESHOLD = 0.25;
    const NDVI_PEAK_THRESHOLD      = 0.70;
    const NDVI_MATURITY_THRESHOLD  = 0.50;
    const NDVI_HARVEST_THRESHOLD   = 0.25;

    /**
     * Parse a CSV dataset file and extract NDVI records + crop cycle parameters.
     */
    public function processDataset(Dataset $dataset): CropCycle
    {
        $filePath = storage_path('app/private/' . $dataset->file_path);

        $records = match ($dataset->type) {
            'CSV'     => $this->parseCsv($filePath),
            'GeoTIFF' => $this->simulateGeoTiffRecords($dataset),
            default   => $this->simulateGeoTiffRecords($dataset),
        };

        // Update dataset record count
        $dataset->update(['record_count' => count($records)]);

        // Create the CropCycle and persist extracted parameters
        return $this->extractAndStoreCycleParameters($dataset, $records);
    }

    /**
     * Parse a CSV file and return array of ['date'=>..., 'ndvi'=>..., ...] rows.
     *
     * Expected CSV columns (case-insensitive):
     *   date, ndvi, evi (opt), lai (opt), temperature (opt), rainfall (opt), growth_stage (opt)
     */
    private function parseCsv(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $records = [];
        foreach ($csv->getRecords() as $record) {
            // Normalize keys: trim and convert to lowercase, removing BOM or special characters
            $row = [];
            foreach ($record as $key => $value) {
                if ($key !== null) {
                    $cleanKey = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($key)));
                    $row[$cleanKey] = $value;
                }
            }

            $date = $this->parseDate($row['date'] ?? $row['observation_date'] ?? null);
            if (!$date) continue;

            $ndvi = $this->clampNdvi((float) ($row['ndvi'] ?? $row['ndvi_value'] ?? 0));

            $records[] = [
                'date'          => $date,
                'ndvi'          => $ndvi,
                'evi'           => isset($row['evi'])         ? $this->clampNdvi((float) $row['evi'])         : null,
                'savi'          => isset($row['savi'])        ? $this->clampNdvi((float) $row['savi'])        : null,
                'lai'           => isset($row['lai'])         ? (float) $row['lai']                           : null,
                'temperature'   => isset($row['temperature']) ? (float) $row['temperature']                   : null,
                'rainfall'      => isset($row['rainfall'])    ? (float) $row['rainfall']                      : null,
                'humidity'      => isset($row['humidity'])    ? (float) $row['humidity']                      : null,
                'soil_moisture' => isset($row['soil_moisture']) ? (float) $row['soil_moisture']               : null,
                'growth_stage'  => $row['growth_stage'] ?? null,
                'cloud_cover'   => isset($row['cloud_cover']) ? (float) $row['cloud_cover']                   : null,
                'satellite'     => $row['satellite'] ?? $row['satellite_source'] ?? null,
            ];
        }

        return $records;
    }

    /**
     * Simulate plausible NDVI time-series for GeoTIFF or missing files (demo mode).
     */
    private function simulateGeoTiffRecords(Dataset $dataset): array
    {
        $records = [];
        $startDate = $dataset->data_start_date ?? now()->subYear();
        $endDate   = $dataset->data_end_date   ?? now();

        $current = Carbon::parse($startDate);
        $end     = Carbon::parse($endDate);
        $dayNum  = 0;

        // Typical bell-curve NDVI profile for a cereal crop
        $totalDays = $current->diffInDays($end);
        $peakDay   = (int)($totalDays * 0.6); // peak at 60% of season

        while ($current->lte($end)) {
            $dayNum++;
            $ndvi = $this->bellCurveNdvi($dayNum, $peakDay, $totalDays);

            $records[] = [
                'date'          => $current->toDateString(),
                'ndvi'          => $ndvi,
                'evi'           => round($ndvi * 0.85 + rand(-3, 3) / 100, 4),
                'savi'          => round($ndvi * 0.90, 4),
                'lai'           => round($ndvi * 5.5, 3),
                'temperature'   => rand(18, 38),
                'rainfall'      => $ndvi > 0.4 ? rand(0, 15) : rand(0, 5),
                'humidity'      => rand(40, 90),
                'soil_moisture' => rand(20, 70),
                'growth_stage'  => $this->inferGrowthStage($ndvi, $dayNum, $totalDays),
                'cloud_cover'   => rand(0, 30),
                'satellite'     => 'Sentinel-2 (Simulated)',
            ];

            $current->addDays(8); // 8-day composite (Landsat/MODIS typical)
        }

        return $records;
    }

    /**
     * Bell-curve NDVI simulation: low at start/end, high at peak.
     */
    private function bellCurveNdvi(int $day, int $peakDay, int $totalDays): float
    {
        $sigma = $totalDays / 5;
        $base  = 0.10;
        $peak  = 0.82 + (rand(-5, 5) / 100);   // peak NDVI with noise
        $ndvi  = $base + ($peak - $base) * exp(-0.5 * pow(($day - $peakDay) / $sigma, 2));
        // Add realistic noise
        $noise = rand(-3, 3) / 100;
        return $this->clampNdvi(round($ndvi + $noise, 4));
    }

    /**
     * Infer growth stage from NDVI value and temporal position.
     */
    private function inferGrowthStage(float $ndvi, int $day, int $totalDays): string
    {
        $pct = $day / max($totalDays, 1);
        if ($pct < 0.05)  return 'pre_sowing';
        if ($pct < 0.12)  return 'germination';
        if ($ndvi < self::NDVI_EMERGENCE_THRESHOLD) return 'emergence';
        if ($pct < 0.35)  return 'tillering';
        if ($pct < 0.50)  return 'jointing';
        if ($pct < 0.65)  return 'heading';
        if ($ndvi >= self::NDVI_PEAK_THRESHOLD) return $pct < 0.70 ? 'flowering' : 'grain_filling';
        if ($ndvi > self::NDVI_MATURITY_THRESHOLD) return 'maturity';
        return 'post_harvest';
    }

    /**
     * Extract crop cycle parameters from records and persist to DB.
     */
    private function extractAndStoreCycleParameters(Dataset $dataset, array $records): CropCycle
    {
        $ndviValues = array_column($records, 'ndvi');
        $dates      = array_column($records, 'date');

        // Find key dates using NDVI thresholds
        $sowingDate     = $this->findFirstDateAboveThreshold($records, self::NDVI_SOWING_THRESHOLD);
        $emergenceDate  = $this->findFirstDateAboveThreshold($records, self::NDVI_EMERGENCE_THRESHOLD);
        $peakIdx        = array_search(max($ndviValues), $ndviValues);
        $peakDate       = $dates[$peakIdx] ?? null;
        $harvestDate    = $this->findLastDateAboveThreshold($records, self::NDVI_HARVEST_THRESHOLD);
        $maturityDate   = $this->findLastDateAboveThreshold($records, self::NDVI_MATURITY_THRESHOLD);

        $ndviMax  = count($ndviValues) ? max($ndviValues) : null;
        $ndviMin  = count($ndviValues) ? min($ndviValues) : null;
        $ndviMean = count($ndviValues) ? round(array_sum($ndviValues) / count($ndviValues), 4) : null;

        // Yield prediction based on NDVI peak (empirical model)
        $yieldPrediction = $this->predictYield($ndviMax, $dataset->crop_type);
        $yieldCategory   = match (true) {
            $yieldPrediction >= 4000  => 'high',
            $yieldPrediction >= 2000  => 'medium',
            default                   => 'low',
        };

        // Irrigation suggestions
        $irrigationSuggestions = $this->generateIrrigationSuggestions($records, $sowingDate);

        $cropCycle = CropCycle::create([
            'dataset_id'             => $dataset->id,
            'user_id'                => $dataset->user_id,
            'crop_type'              => $dataset->crop_type ?? 'Unknown',
            'region'                 => $dataset->region ?? 'Unknown',
            'season_year'            => now()->year,
            'season'                 => $this->inferSeason($sowingDate),
            'sowing_date'            => $sowingDate,
            'emergence_date'         => $emergenceDate,
            'peak_growth_date'       => $peakDate,
            'maturity_date'          => $maturityDate,
            'harvest_date'           => $harvestDate,
            'ndvi_max'               => $ndviMax,
            'ndvi_min'               => $ndviMin,
            'ndvi_mean'              => $ndviMean,
            'ndvi_at_sowing'         => $sowingDate ? $this->getNdviAtDate($records, $sowingDate) : null,
            'ndvi_at_peak'           => $ndviMax,
            'ndvi_at_harvest'        => $harvestDate ? $this->getNdviAtDate($records, $harvestDate) : null,
            'yield_prediction'       => $yieldPrediction,
            'yield_category'         => $yieldCategory,
            'irrigation_suggestions' => $irrigationSuggestions,
            'status'                 => 'active',
        ]);

        // Persist individual NDVI records
        foreach ($records as $rec) {
            NdviRecord::create([
                'crop_cycle_id'    => $cropCycle->id,
                'observation_date' => $rec['date'],
                'ndvi_value'       => $rec['ndvi'],
                'evi_value'        => $rec['evi'],
                'savi_value'       => $rec['savi'],
                'lai_value'        => $rec['lai'],
                'growth_stage'     => $rec['growth_stage'],
                'temperature'      => $rec['temperature'],
                'rainfall'         => $rec['rainfall'],
                'humidity'         => $rec['humidity'],
                'soil_moisture'    => $rec['soil_moisture'],
                'satellite_source' => $rec['satellite'] ?? null,
                'cloud_cover'      => $rec['cloud_cover'],
                'day_of_year'      => Carbon::parse($rec['date'])->dayOfYear,
            ]);
        }

        return $cropCycle;
    }

    // ─── Helper methods ──────────────────────────────────────────────────────

    private function findFirstDateAboveThreshold(array $records, float $threshold): ?string
    {
        foreach ($records as $rec) {
            if ((float)$rec['ndvi'] >= $threshold) {
                return is_string($rec['date']) ? $rec['date'] : $rec['date']->toDateString();
            }
        }
        return null;
    }

    private function findLastDateAboveThreshold(array $records, float $threshold): ?string
    {
        $result = null;
        foreach ($records as $rec) {
            if ((float)$rec['ndvi'] >= $threshold) {
                $result = is_string($rec['date']) ? $rec['date'] : $rec['date']->toDateString();
            }
        }
        return $result;
    }

    private function getNdviAtDate(array $records, string $date): ?float
    {
        foreach ($records as $rec) {
            $recDate = is_string($rec['date']) ? $rec['date'] : $rec['date']->toDateString();
            if ($recDate === $date) return (float) $rec['ndvi'];
        }
        return null;
    }

    /**
     * Simple empirical yield model: Yield (kg/ha) ≈ NDVI_peak × crop_factor × area_factor
     */
    private function predictYield(?float $ndviMax, ?string $cropType): float
    {
        $cropFactors = [
            'wheat'  => 6200,
            'rice'   => 5800,
            'maize'  => 7500,
            'cotton' => 2500,
            'sugarcane' => 65000,
            'soybean'   => 3200,
            'default'   => 4000,
        ];
        $factor = $cropFactors[strtolower($cropType ?? 'default')] ?? $cropFactors['default'];
        return round(($ndviMax ?? 0.5) * $factor * (1 + rand(-10, 10) / 100), 2);
    }

    private function generateIrrigationSuggestions(array $records, ?string $sowingDate): array
    {
        $suggestions = [];
        foreach ($records as $i => $rec) {
            $soilMoisture = $rec['soil_moisture'] ?? 50;
            if ((float)$soilMoisture < 30) {
                $suggestions[] = [
                    'date'   => is_string($rec['date']) ? $rec['date'] : $rec['date']->toDateString(),
                    'stage'  => $rec['growth_stage'] ?? 'unknown',
                    'action' => 'Irrigate — soil moisture critically low (' . round($soilMoisture, 1) . '%)',
                    'amount' => '25–35 mm',
                ];
            }
        }
        return array_slice($suggestions, 0, 5); // return top 5 events
    }

    private function inferSeason(?string $sowingDate): string
    {
        if (!$sowingDate) return 'Kharif';
        $month = Carbon::parse($sowingDate)->month;
        return match (true) {
            in_array($month, [6, 7, 8, 9])   => 'Kharif',
            in_array($month, [10, 11, 12, 1]) => 'Rabi',
            default                           => 'Zaid',
        };
    }

    private function parseDate(mixed $value): ?string
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function clampNdvi(float $v): float
    {
        return round(max(-1.0, min(1.0, $v)), 4);
    }
}
