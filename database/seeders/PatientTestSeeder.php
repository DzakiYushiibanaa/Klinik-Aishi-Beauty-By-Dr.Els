<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PatientTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'pasien@test.com'],
            [
                'name' => 'Pasien Test',
                'password' => Hash::make('password123'),
            ]
        );

        $user->assignRole('pasien');
    }
}
