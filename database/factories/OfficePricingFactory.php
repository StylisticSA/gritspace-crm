<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfficePricing>
 */
class OfficePricingFactory extends Factory
{
    protected $model = OfficePricing::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition()
    {
        // Create only two OfficePricing records: one for premium and one for standard
        OfficePricing::create([
            'pricing_type' => 'premium',
            'rate' => 3300,  
        ]);

        OfficePricing::create([
            'pricing_type' => 'standard',
            'rate' => 5720,  
        ]);
    }
}
