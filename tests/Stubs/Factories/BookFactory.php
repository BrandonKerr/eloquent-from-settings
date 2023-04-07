<?php

namespace Brandonkerr\EloquentFromSettings\Tests\Stubs\Factories;

use Brandonkerr\EloquentFromSettings\Tests\Stubs\Models\Author;
use Brandonkerr\EloquentFromSettings\Tests\Stubs\Models\Book;
use Brandonkerr\EloquentFromSettings\Tests\Stubs\Models\Review;
use Brandonkerr\EloquentFromSettings\Traits\FromSettings;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    use FromSettings;

    /**
     * The factory's corresponding model.
     * Needed for handling our stub namespace
     *
     * @var string
     */
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title" => $this->faker->words(rand(3, 10), true),
            "author_id" => Author::factory(),
        ];
    }

    /**
     * Custom function to find or create the author, based on the given name
     *
     * @param string $name
     * @return $this
     */
    public function forAuthor(string $name): self
    {
        $author = Author::firstOrCreate([
            "name" => $name
        ]);

        return $this->state(["author_id" => $author->id]);
    }

    /**
     * Custom function to add reviews with a perfect score
     *
     * @param string ...$names
     * @return $this
     */
    public function perfectReviews(string ...$names): self
    {
        return $this->has(
            Review::factory()
                ->count(count($names))
                ->sequence(fn (Sequence $sequence) => [
                    "reviewer" => $names[$sequence->index],
                    "score" => 100,
                ])
        );
    }
}
