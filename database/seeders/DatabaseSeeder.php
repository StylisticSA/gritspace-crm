<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\OfficeSeeder;
use Database\Seeders\AmenitySeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\DailyUsageSeeder;
use Database\Seeders\OfficePricingSeeder;
use Database\Seeders\BoardroomBookingsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            CategorySeeder::class,
            // AmenitySeeder::class,
            // OfficePricingSeeder::class,
            // OfficeSeeder::class,
            UserSeeder::class,
            RolesAndPermissionsSeeder::class,
            // NoteSeeder::class,
            // BoardroomBookingsSeeder::class,
            // DailyUsageSeeder::class
        ]);
    }
}
