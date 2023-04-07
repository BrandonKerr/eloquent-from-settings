<?php

namespace Brandonkerr\EloquentFromSettings\Tests;

use Brandonkerr\EloquentFromSettings\Helpers\FindMethods;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase;

class FindMethodsTest extends TestCase
{
    /**
     * Ensure the getFactoryMethods helper function is able to find methods added to the factory class
     *
     * @return void
     * @test
     */
    public function getFactoryMethodsWorks(): void
    {
        $factory = new MyModelFactory();
        $factoryMethods = FindMethods::getFactoryMethods($factory);
        $this->assertContains("setName", $factoryMethods);
    }
}


class MyModel extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return MyModelFactory
     */
    protected static function newFactory(): MyModelFactory
    {
        return MyModelFactory::new();
    }

}

class MyModelFactory extends Factory
{
    protected $model = MyModel::class;

    /**
     * Custom function to se the name of this MyModel
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->state(["name" => $name]);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, string>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name,
        ];
    }
}
