<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@cropscycle.com'],
            [
                'name'         => 'Admin User',
                'password'     => Hash::make('password'),
                'role'         => 'admin',
                'organization' => 'CropsCycle HQ',
                'region'       => 'New Delhi',
                'is_active'    => true,
                'locale'       => 'en',
                'theme'        => 'dark',
            ]
        );
        $admin->assignRole('admin');

        // Researcher users
        $researchers = [
            ['name' => 'Dr. Priya Sharma',    'email' => 'priya@icar.gov.in',      'org' => 'ICAR',         'region' => 'Punjab'],
            ['name' => 'Dr. Arjun Reddy',     'email' => 'arjun@iari.res.in',      'org' => 'IARI',         'region' => 'Haryana'],
            ['name' => 'Dr. Meera Iyer',      'email' => 'meera@isro.gov.in',      'org' => 'ISRO SAC',     'region' => 'Gujarat'],
        ];

        foreach ($researchers as $data) {
            $r = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'         => $data['name'],
                    'password'     => Hash::make('password'),
                    'role'         => 'researcher',
                    'organization' => $data['org'],
                    'region'       => $data['region'],
                    'is_active'    => true,
                    'locale'       => 'en',
                ]
            );
            $r->assignRole('researcher');
        }

        // Farmer users
        $farmers = [
            ['name' => 'Ramesh Kumar',    'email' => 'ramesh@farm.in',    'region' => 'Punjab'],
            ['name' => 'Suresh Patel',    'email' => 'suresh@farm.in',    'region' => 'Gujarat'],
            ['name' => 'Lakshmi Devi',    'email' => 'lakshmi@farm.in',   'region' => 'Andhra Pradesh'],
            ['name' => 'Gurpreet Singh',  'email' => 'gurpreet@farm.in',  'region' => 'Haryana'],
            ['name' => 'Anjali Verma',    'email' => 'anjali@farm.in',    'region' => 'MP'],
        ];

        foreach ($farmers as $data) {
            $f = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => Hash::make('password'),
                    'role'      => 'farmer',
                    'region'    => $data['region'],
                    'is_active' => true,
                    'locale'    => 'en',
                ]
            );
            $f->assignRole('farmer');
        }

        $this->command->info('✅ 9 users seeded (1 admin, 3 researchers, 5 farmers)');
    }
}
