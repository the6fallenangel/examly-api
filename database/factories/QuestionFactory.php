<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(QuestionType::cases());

        return [
            'exam_id' => Exam::factory(),
            'type' => $type,
            'prompt' => fake()->sentence().'?',
            'options' => in_array($type, [QuestionType::MultipleChoice, QuestionType::Checkbox])
                ? fake()->words(4)
                : null,
            'config' => null,
            'sort_order' => 0,
            'is_required' => true,
        ];
    }

    public function type(QuestionType $type): static
    {
        return $this->state(fn (array $attributes) => ['type' => $type]);
    }
}
