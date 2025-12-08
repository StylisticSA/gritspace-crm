<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\Location;
use App\Models\OfficePricing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class OfficeFactory extends Factory
{
    protected $model = Office::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),  
            'office_type' => $this->faker->randomElement(['closed', 'dedicated']), 
            'seats' => $this->faker->optional()->numberBetween(1, 10),  
            'monthly_rate' => $this->faker->numberBetween(1000, 3000),  
            'daily_rate' => $this->faker->optional()->numberBetween(100, 500),  
            'pricing_id' => OfficePricing::factory(), 
        
        ];
    }
}
