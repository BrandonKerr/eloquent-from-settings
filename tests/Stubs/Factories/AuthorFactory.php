<?php

namespace Brandonkerr\EloquentFromSettings\Tests\Stubs\Factories;

use Brandonkerr\EloquentFromSettings\Tests\Stubs\Models\Author;
use Brandonkerr\EloquentFromSettings\Traits\FromSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    use FromSettings;

    /**
     * The factory's corresponding model.
     * Needed for handling our stub namespace
     *
     * @var string
     */
    protected $model = Author::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name(),
        ];
    }
}
