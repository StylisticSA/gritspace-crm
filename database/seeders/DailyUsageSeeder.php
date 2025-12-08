<?php

namespace Database\Seeders;

use App\Models\DailyUsage;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DailyUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DailyUsage::factory()->count(20)->create();
    }

}
