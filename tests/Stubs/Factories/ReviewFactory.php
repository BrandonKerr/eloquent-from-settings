<?php

namespace Brandonkerr\EloquentFromSettings\Tests\Stubs\Factories;

use Brandonkerr\EloquentFromSettings\Tests\Stubs\Models\Book;
use Brandonkerr\EloquentFromSettings\Tests\Stubs\Models\Review;
use Brandonkerr\EloquentFromSettings\Traits\FromSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    use FromSettings;

    /**
     * The factory's corresponding model.
     * Needed for handling our stub namespace
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "reviewer" => $this->faker->name,
            "score" => $this->faker->numberBetween(0, 100),
            "book_id" => Book::factory()
        ];
    }
}
