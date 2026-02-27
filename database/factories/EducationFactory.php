<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Education>
 */
class EducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution' => fake()->company(),
            'degree' => fake()->jobTitle(),
            'start_date' => fake()->dateTimeBetween('-5 years', '-2 years')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'type' => fake()->randomElement(['education', 'certificate']),
            'url' => fake()->url(),
            'description' => fake()->paragraph(),
        ];
    }
}
