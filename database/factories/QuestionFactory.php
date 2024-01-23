<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->text(),
            'type' => QuestionType::MultipleChoice,
            'is_published' => $this->faker->boolean(),
            'category_id' => QuestionCategoryFactory::new(),
        ];
    }
}
