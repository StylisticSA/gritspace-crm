<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Location;
use App\Models\DailyUsage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyUsage>
 */

class DailyUsageFactory extends Factory
{
    protected $model = DailyUsage::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['coffee', 'printing']);

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'location_id' => Location::inRandomOrder()->first()->id,
            'type' => $type,
            'amount' => $type === 'coffee'
                ? $this->faker->numberBetween(1, 5)
                : $this->faker->numberBetween(10, 100),
             'date' => $this->faker->dateTimeBetween(
                 now()->startOfMonth(),
                 now()->day(22)->endOfDay()
             )->format('Y-m-d'),
        ];
    }
}
