<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\BoardroomBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardroomBooking>
 */
class BoardroomBookingFactory extends Factory
{
    protected $model = BoardroomBooking::class;

    public function definition()
    {
        $statuses = ['pending', 'approved', 'rejected', 'cancelled', 'paid'];
        $plans = ['Basic', 'Standard', 'Premium'];

        // Generate random selected dates and times
        $selectedDates = [
            Carbon::now()->addDays($this->faker->numberBetween(1, 30))->toDateString(),
            Carbon::now()->addDays($this->faker->numberBetween(31, 60))->toDateString(),
        ];

        $selectedTimes = [
            ['start' => '09:00', 'end' => '11:00'],
            ['start' => '14:00', 'end' => '16:00'],
        ];

        return [
            'user_id' => $this->faker->numberBetween(1, 10),       // adjust according to your users
            'boardroom_id' => $this->faker->numberBetween(1, 5),   // adjust according to your boardrooms
            'plan' => $plans[array_rand($plans)],
            'selected_dates' => json_encode($selectedDates),
            'selected_times' => json_encode($selectedTimes),
            'months' => $this->faker->numberBetween(1, 12),
            'selected_price' => $this->faker->randomFloat(2, 100, 1000),
            'status' => $statuses[array_rand($statuses)],
        ];
    }
}
