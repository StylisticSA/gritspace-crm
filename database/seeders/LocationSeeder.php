<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::create([
            'name' => 'The Village',
            'address' => '123 Main St.',
            'city' => 'Johannesburg',
        ]);

        Location::create([
            'name' => 'Rosemary',
            'address' => '456 Rose Ave.',
            'city' => 'Johannesburg',
        ]);

    }
}
