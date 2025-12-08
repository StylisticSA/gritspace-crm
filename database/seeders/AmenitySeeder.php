<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            [
                'amenity_name' => 'Free Wi-Fi',
                'price' => 0.00,
                'description' => 'High-speed internet access in all areas.',
            ],
            [
                'amenity_name' => 'Parking',
                'price' => 20.00,
                'description' => 'Secure parking for vehicles.',
            ],
            [
                'amenity_name' => 'Conference Room',
                'price' => 100.00,
                'description' => 'Fully equipped conference room for meetings and events.',
            ],
            [
                'amenity_name' => 'Coffee Machine',
                'price' => 5.00,
                'description' => 'Enjoy freshly brewed coffee all day.',
            ],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create([
                'amenity_name' => $amenity['amenity_name'],
                'price' => $amenity['price'],
                'description' => $amenity['description'],
            ]);
        }
    }
}
