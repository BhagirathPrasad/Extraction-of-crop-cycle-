<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DatasetFactory extends Factory
{
    public function definition(): array
    {
        $cropTypes = ['wheat', 'rice', 'maize', 'cotton', 'sugarcane', 'soybean', 'barley', 'millet'];
        $regions   = ['Punjab', 'Haryana', 'Uttar Pradesh', 'Madhya Pradesh', 'Maharashtra', 'Gujarat', 'Rajasthan'];
        $statuses  = ['pending', 'processing', 'processed', 'failed'];
        $type      = $this->faker->randomElement(['CSV', 'CSV', 'CSV', 'GeoTIFF']); // mostly CSV

        $startDate = $this->faker->dateTimeBetween('-2 years', '-6 months');
        $endDate   = $this->faker->dateTimeBetween('-5 months', 'now');

        return [
            'user_id'           => User::factory(),
            'name'              => $this->faker->randomElement($cropTypes) . ' Dataset ' . $this->faker->year,
            'description'       => $this->faker->sentence,
            'type'              => $type,
            'file_path'         => 'datasets/' . $this->faker->uuid . '.csv',
            'original_filename' => $this->faker->word . '.csv',
            'file_size'         => $this->faker->numberBetween(5000, 5000000),
            'crop_type'         => $this->faker->randomElement($cropTypes),
            'region'            => $this->faker->randomElement($regions),
            'country'           => 'India',
            'latitude'          => $this->faker->latitude(8, 37),
            'longitude'         => $this->faker->longitude(68, 97),
            'data_start_date'   => $startDate,
            'data_end_date'     => $endDate,
            'record_count'      => $this->faker->numberBetween(10, 500),
            'status'            => $this->faker->randomElement($statuses),
            'processed_at'      => $this->faker->optional()->dateTimeBetween('-4 months', 'now'),
        ];
    }

    public function processed(): static
    {
        return $this->state(['status' => 'processed', 'processed_at' => now()]);
    }
}
