<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'taker_email' => fake()->unique()->safeEmail(),
            'taker_name' => fake()->name(),
            'verified_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'ip_address' => fake()->ipv4(),
            'token' => Str::random(64),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now(),
        ]);
    }

    public function started(): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
        ]);
    }
}
