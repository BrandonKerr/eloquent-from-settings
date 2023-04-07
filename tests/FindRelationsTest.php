<?php

namespace Brandonkerr\EloquentFromSettings\Tests;

use Brandonkerr\EloquentFromSettings\Helpers\FindRelations;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Orchestra\Testbench\TestCase;
use ReflectionException;

class FindRelationsTest extends TestCase
{
    /**
     * Ensure the getFactoryMethods helper function is able to find methods added to the factory's model class
     *
     * @return void
     * @test
     * @throws ReflectionException
     */
    public function getModelRelationsWorks(): void
    {
        $factory = new FilmFactory();
        $relationships = FindRelations::getModelRelations($factory);
        $this->assertContains("director", $relationships);
    }

    /**
     * Ensure the guessModelRelation helper function is able to find methods added to the factory's model class
     *
     * @return void
     * @test
     */
    public function guessModelRelationWorks(): void
    {
        $factory = new FilmFactory();
        $hasActors = FindRelations::guessModelRelation($factory, "actors");
        $this->assertTrue($hasActors);
        $hasProducers = FindRelations::guessModelRelation($factory, "producers");
        $this->assertFalse($hasProducers);
    }
}

class Film extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return FilmFactory
     */
    protected static function newFactory(): FilmFactory
    {
        return FilmFactory::new();
    }

    public function director(): HasOne
    {
        return $this->hasOne(Director::class);
    }

    /* intentionally leaving off return type to test guessModelRelation() */
    public function actors()
    {
        return $this->hasMany(Actor::class);
    }

}

class Director extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return DirectorFactory
     */
    protected static function newFactory(): DirectorFactory
    {
        return DirectorFactory::new();
    }

}

class Actor extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return ActorFactory
     */
    protected static function newFactory(): ActorFactory
    {
        return ActorFactory::new();
    }

}

class FilmFactory extends Factory
{
    protected $model = Film::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            "title" => $this->faker->words(3, true),
        ];
    }
}

class DirectorFactory extends Factory
{
    protected $model = Director::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name,
        ];
    }
}

class ActorFactory extends Factory
{
    protected $model = Actor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            "title" => $this->faker->name,
        ];
    }
}
