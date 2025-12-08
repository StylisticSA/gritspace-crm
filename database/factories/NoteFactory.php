<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Note::class;

    public function definition(): array
    {

        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'office_name' => $this->faker->name(),
            'content' => $this->faker->sentence(),
            'is_visible_to_user' => $this->faker->boolean(50),
            'created_by' => $this->faker->name(),
        ];
    }
}
