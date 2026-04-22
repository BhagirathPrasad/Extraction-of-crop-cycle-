<?php

namespace Database\Factories;

use App\Models\Dataset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CropCycleFactory extends Factory
{
    public function definition(): array
    {
        $cropTypes  = ['wheat', 'rice', 'maize', 'cotton', 'sugarcane', 'soybean'];
        $regions    = ['Punjab', 'Haryana', 'UP', 'MP', 'Maharashtra', 'Gujarat'];
        $seasons    = ['Kharif', 'Rabi', 'Zaid'];

        $sowingDate   = $this->faker->dateTimeBetween('-18 months', '-6 months');
        $harvestDate  = (clone $sowingDate)->modify('+' . $this->faker->numberBetween(90, 180) . ' days');
        $peakDate     = (clone $sowingDate)->modify('+' . $this->faker->numberBetween(50, 90) . ' days');

        $ndviMax  = $this->faker->randomFloat(4, 0.55, 0.92);
        $ndviMin  = $this->faker->randomFloat(4, 0.05, 0.20);
        $ndviMean = ($ndviMax + $ndviMin) / 2;

        $cropType        = $this->faker->randomElement($cropTypes);
        $yieldPrediction = $ndviMax * match($cropType) {
            'wheat'     => 6200, 'rice' => 5800, 'maize' => 7500,
            'sugarcane' => 65000, 'cotton' => 2500, default => 4000
        } * $this->faker->randomFloat(2, 0.9, 1.1);

        return [
            'dataset_id'         => Dataset::factory()->processed(),
            'user_id'            => User::factory(),
            'crop_type'          => $cropType,
            'variety'            => $cropType . '-' . $this->faker->bothify('??-##'),
            'region'             => $this->faker->randomElement($regions),
            'field_id'           => 'FLD-' . $this->faker->numerify('####'),
            'season_year'        => $this->faker->numberBetween(2022, 2025),
            'season'             => $this->faker->randomElement($seasons),
            'sowing_date'        => $sowingDate,
            'emergence_date'     => (clone $sowingDate)->modify('+10 days'),
            'tillering_date'     => (clone $sowingDate)->modify('+30 days'),
            'peak_growth_date'   => $peakDate,
            'maturity_date'      => (clone $harvestDate)->modify('-15 days'),
            'harvest_date'       => $harvestDate,
            'ndvi_max'           => $ndviMax,
            'ndvi_min'           => $ndviMin,
            'ndvi_mean'          => round($ndviMean, 4),
            'ndvi_at_sowing'     => $this->faker->randomFloat(4, 0.08, 0.20),
            'ndvi_at_peak'       => $ndviMax,
            'ndvi_at_harvest'    => $this->faker->randomFloat(4, 0.15, 0.35),
            'yield_prediction'   => round($yieldPrediction, 2),
            'yield_category'     => $yieldPrediction >= 4000 ? 'high' : ($yieldPrediction >= 2000 ? 'medium' : 'low'),
            'actual_yield'       => $this->faker->optional(0.4)->randomFloat(2, 1000, 8000),
            'status'             => 'completed',
        ];
    }
}
