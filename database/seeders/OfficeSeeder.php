<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Category;
use App\Models\Location;
use Faker\Factory as Faker;
use App\Models\OfficePricing;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $faker = Faker::create();

        // Create 4 Closed offices
        for ($i = 1; $i <= 4; $i++) {
            Office::create([
                'location_id' => Location::inRandomOrder()->first()->id,
                'category_id' => Category::inRandomOrder()->first()->id,
                'seats' => $faker->optional()->numberBetween(1, 10),
                'monthly_rate' => $faker->numberBetween(1000, 3000),
                'daily_rate' => $faker->optional()->numberBetween(100, 500),
                'office_name' => 'Closed office ' . $i,
            ]);
        }

        // Create 4 Dedicated Desks
        for ($i = 1; $i <= 4; $i++) {
            Office::create([
                'location_id' => Location::inRandomOrder()->first()->id,
                'category_id' => Category::inRandomOrder()->first()->id,
                'seats' => $faker->optional()->numberBetween(1, 10),
                'monthly_rate' => $faker->numberBetween(1000, 3000),
                'daily_rate' => $faker->optional()->numberBetween(100, 500),
                'office_name' => 'Dedicated Desks ' . $i,
            ]);
        }

    }
}
