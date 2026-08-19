<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraph(),
            'required_skills' => $this->faker->words(5, true),
            'category' => $this->faker->randomElement(['Programming', 'Design', 'Marketing', 'Data']),
            'location' => $this->faker->randomElement(['Cairo', 'Alexandria', 'Giza']),
            'work_type' => $this->faker->randomElement(['Remote', 'On-site', 'Hybrid']),
            'salary' => $this->faker->numberBetween(8000, 40000),
            'application_deadline' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
        ];
    }
}