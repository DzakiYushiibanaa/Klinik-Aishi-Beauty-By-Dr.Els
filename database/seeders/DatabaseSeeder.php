<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AishiBeautySeeder::class,
            // DummyTransaksiSeeder::class, // uncomment to seed sample transactions
        ]);
    }
}
