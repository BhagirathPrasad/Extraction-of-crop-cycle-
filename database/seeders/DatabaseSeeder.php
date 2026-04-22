<?php

namespace Database\Seeders;

use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\NdviRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
            DatasetsSeeder::class,
            CropCyclesSeeder::class,
        ]);
    }
}
