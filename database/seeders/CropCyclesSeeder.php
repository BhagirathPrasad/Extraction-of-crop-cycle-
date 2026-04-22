<?php

namespace Database\Seeders;

use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\NdviRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CropCyclesSeeder extends Seeder
{
    public function run(): void
    {
        $datasets = Dataset::where('status', 'processed')->get();

        if ($datasets->isEmpty()) {
            $this->command->warn('No processed datasets. Run DatasetsSeeder first.');
            return;
        }

        foreach ($datasets as $dataset) {
            // Create one crop cycle per dataset
            $sowingDate  = Carbon::parse($dataset->data_start_date)->addDays(5);
            $harvestDate = Carbon::parse($dataset->data_end_date)->subDays(5);
            $peakDate    = $sowingDate->copy()->addDays((int)($sowingDate->diffInDays($harvestDate) * 0.6));

            $ndviMax  = round(rand(65, 90) / 100, 4);
            $ndviMin  = round(rand(5, 15) / 100, 4);
            $ndviMean = round(($ndviMax + $ndviMin) / 2, 4);

            $cropFactors = [
                'wheat' => 6200, 'rice' => 5800, 'maize' => 7500,
                'cotton' => 2500, 'sugarcane' => 65000, 'soybean' => 3200, 'barley' => 4000,
            ];
            $factor = $cropFactors[$dataset->crop_type] ?? 4000;
            $yield  = round($ndviMax * $factor * (1 + rand(-10, 10) / 100), 2);

            $cropCycle = CropCycle::firstOrCreate(
                ['dataset_id' => $dataset->id, 'season_year' => Carbon::parse($dataset->data_start_date)->year],
                [
                    'user_id'            => $dataset->user_id,
                    'crop_type'          => $dataset->crop_type,
                    'variety'            => ucfirst($dataset->crop_type) . '-' . rand(100, 999),
                    'region'             => $dataset->region,
                    'field_id'           => 'FLD-' . rand(1000, 9999),
                    'season'             => $sowingDate->month >= 6 && $sowingDate->month <= 9 ? 'Kharif' : 'Rabi',
                    'sowing_date'        => $sowingDate,
                    'emergence_date'     => $sowingDate->copy()->addDays(12),
                    'tillering_date'     => $sowingDate->copy()->addDays(35),
                    'jointing_date'      => $sowingDate->copy()->addDays(55),
                    'heading_date'       => $sowingDate->copy()->addDays(75),
                    'peak_growth_date'   => $peakDate,
                    'maturity_date'      => $harvestDate->copy()->subDays(15),
                    'harvest_date'       => $harvestDate,
                    'ndvi_max'           => $ndviMax,
                    'ndvi_min'           => $ndviMin,
                    'ndvi_mean'          => $ndviMean,
                    'ndvi_at_sowing'     => round(rand(8, 18) / 100, 4),
                    'ndvi_at_peak'       => $ndviMax,
                    'ndvi_at_harvest'    => round(rand(15, 30) / 100, 4),
                    'yield_prediction'   => $yield,
                    'yield_category'     => $yield >= 4000 ? 'high' : ($yield >= 2000 ? 'medium' : 'low'),
                    'actual_yield'       => rand(0, 1) ? round($yield * (rand(85, 115) / 100), 2) : null,
                    'irrigation_suggestions' => $this->generateIrrigationSuggestions($sowingDate),
                    'status'             => 'completed',
                ]
            );

            // Generate NDVI time-series records (every 8 days)
            $this->seedNdviRecords($cropCycle, $sowingDate, $harvestDate, $ndviMax);
        }

        $this->command->info('✅ Crop cycles + NDVI records seeded for all processed datasets.');
    }

    private function seedNdviRecords(CropCycle $cropCycle, Carbon $start, Carbon $end, float $peakNdvi): void
    {
        if ($cropCycle->ndviRecords()->exists()) return;

        $totalDays = $start->diffInDays($end);
        $peakDay   = (int)($totalDays * 0.60);
        $current   = $start->copy();
        $dayNum    = 0;

        $stages = ['germination', 'emergence', 'tillering', 'jointing', 'heading', 'grain_filling', 'maturity', 'post_harvest'];

        while ($current->lte($end)) {
            $dayNum++;
            $sigma = $totalDays / 5;
            $ndvi  = round(0.10 + ($peakNdvi - 0.10) * exp(-0.5 * pow(($dayNum - $peakDay) / $sigma, 2)) + rand(-3, 3) / 100, 4);
            $ndvi  = max(-1.0, min(1.0, $ndvi));

            $pct   = $dayNum / max($totalDays, 1);
            $stage = match(true) {
                $pct < 0.08  => 'germination',
                $pct < 0.20  => 'emergence',
                $pct < 0.40  => 'tillering',
                $pct < 0.55  => 'jointing',
                $pct < 0.70  => 'heading',
                $pct < 0.80  => 'grain_filling',
                $pct < 0.92  => 'maturity',
                default      => 'post_harvest',
            };

            NdviRecord::create([
                'crop_cycle_id'    => $cropCycle->id,
                'observation_date' => $current->toDateString(),
                'ndvi_value'       => $ndvi,
                'evi_value'        => round($ndvi * 0.87 + rand(-2, 2) / 100, 4),
                'savi_value'       => round($ndvi * 0.91, 4),
                'lai_value'        => round(max(0, $ndvi * 5.2), 3),
                'growth_stage'     => $stage,
                'temperature'      => rand(18, 40),
                'rainfall'         => ($ndvi > 0.4 && rand(0, 1)) ? rand(5, 40) : 0,
                'humidity'         => rand(40, 90),
                'soil_moisture'    => rand(25, 75),
                'day_of_year'      => $current->dayOfYear,
                'satellite_source' => 'Sentinel-2',
                'cloud_cover'      => rand(0, 20),
            ]);

            $current->addDays(8);
        }
    }

    private function generateIrrigationSuggestions(Carbon $sowingDate): array
    {
        return [
            ['date' => $sowingDate->copy()->addDays(15)->toDateString(), 'stage' => 'emergence', 'action' => 'Light irrigation — 20mm', 'amount' => '20 mm'],
            ['date' => $sowingDate->copy()->addDays(40)->toDateString(), 'stage' => 'tillering', 'action' => 'Irrigate — 30mm for tillering boost', 'amount' => '30 mm'],
            ['date' => $sowingDate->copy()->addDays(70)->toDateString(), 'stage' => 'jointing',  'action' => 'Critical irrigation — 35mm', 'amount' => '35 mm'],
        ];
    }
}
