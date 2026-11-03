<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Dev-only pilot data (item 70).
     */
    public function run(): void
    {
        $this->call(DevPilotSeeder::class);
    }
}
