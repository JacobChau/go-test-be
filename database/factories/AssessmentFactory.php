<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'thumbnail' => fake()->imageUrl(),
            'is_published' => fake()->boolean(),
            'duration' => fake()->numberBetween(1, 100),
            'total_marks' => fake()->numberBetween(1, 100),
            'valid_from' => fake()->dateTime(),
            'valid_to' => fake()->dateTime(),
            'subject_id' => SubjectFactory::new(),
            'created_by' => UserFactory::new(),
        ];
    }
}
