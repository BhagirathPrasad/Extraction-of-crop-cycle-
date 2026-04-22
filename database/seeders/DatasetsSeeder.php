<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatasetsSeeder extends Seeder
{
    protected array $crops    = ['wheat', 'rice', 'maize', 'cotton', 'soybean', 'barley'];
    protected array $regions  = ['Punjab', 'Haryana', 'UP', 'MP', 'Gujarat', 'Rajasthan'];

    public function run(): void
    {
        $researchers = User::where('role', 'researcher')->get();
        $farmers     = User::where('role', 'farmer')->get();
        $users       = $researchers->merge($farmers);

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run UsersSeeder first.');
            return;
        }

        $sampleDatasets = [
            ['crop' => 'wheat',    'region' => 'Punjab',    'start' => '2024-10-15', 'end' => '2025-04-10'],
            ['crop' => 'rice',     'region' => 'Haryana',   'start' => '2024-06-20', 'end' => '2024-11-05'],
            ['crop' => 'maize',    'region' => 'UP',        'start' => '2024-07-01', 'end' => '2024-10-25'],
            ['crop' => 'cotton',   'region' => 'Gujarat',   'start' => '2024-05-15', 'end' => '2025-01-20'],
            ['crop' => 'soybean',  'region' => 'MP',        'start' => '2024-06-25', 'end' => '2024-10-15'],
            ['crop' => 'wheat',    'region' => 'Rajasthan', 'start' => '2024-11-01', 'end' => '2025-04-20'],
            ['crop' => 'rice',     'region' => 'Punjab',    'start' => '2023-06-15', 'end' => '2023-11-01'],
            ['crop' => 'barley',   'region' => 'Haryana',   'start' => '2024-10-20', 'end' => '2025-03-30'],
            ['crop' => 'maize',    'region' => 'Gujarat',   'start' => '2024-07-10', 'end' => '2024-11-15'],
            ['crop' => 'cotton',   'region' => 'MP',        'start' => '2024-04-25', 'end' => '2024-12-30'],
            ['crop' => 'wheat',    'region' => 'UP',        'start' => '2025-11-10', 'end' => '2025-04-01'],
            ['crop' => 'soybean',  'region' => 'Rajasthan', 'start' => '2024-07-01', 'end' => '2024-10-20'],
        ];

        foreach ($sampleDatasets as $i => $data) {
            $user = $users[$i % $users->count()];

            Dataset::firstOrCreate(
                ['name' => ucfirst($data['crop']) . ' Season Dataset — ' . $data['region']],
                [
                    'user_id'           => $user->id,
                    'description'       => "Multi-temporal satellite data for {$data['crop']} in {$data['region']}.",
                    'type'              => 'CSV',
                    'file_path'         => 'datasets/demo/' . $data['crop'] . '_' . $i . '.csv',
                    'original_filename' => $data['crop'] . '_sentinel2.csv',
                    'file_size'         => rand(50000, 2000000),
                    'crop_type'         => $data['crop'],
                    'region'            => $data['region'],
                    'country'           => 'India',
                    'latitude'          => round(rand(20, 35) + rand(0, 99) / 100, 4),
                    'longitude'         => round(rand(70, 90) + rand(0, 99) / 100, 4),
                    'data_start_date'   => $data['start'],
                    'data_end_date'     => $data['end'],
                    'record_count'      => rand(30, 150),
                    'status'            => 'processed',
                    'processed_at'      => now()->subDays(rand(1, 30)),
                ]
            );
        }

        $this->command->info('✅ 12 sample datasets seeded.');
    }
}
