<?php

namespace Brandonkerr\EloquentFromSettings\Traits;

use Brandonkerr\EloquentFromSettings\Helpers\FindMethods;
use Brandonkerr\EloquentFromSettings\Helpers\FindRelations;
use Brandonkerr\EloquentFromSettings\Tests\Stubs\Exceptions\MissingTraitException;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JsonException;
use ReflectionException;

trait FromSettings
{
    protected array $attributes = [];

    protected array $relations = [];

    protected array $customMethods = [];

    /**
     * Use the provided settings to fill this factory's
     * model class' fillable attributes, to generate any related
     * models with the given attributes and nested relationships,
     * and to call any methods in this factory class with the
     * corresponding values as method arguments.
     *
     * @param mixed ...$settings
     *
     * @return Factory
     * @throws MissingTraitException
     * @throws ReflectionException
     */
    public function fromSettingsArray(mixed ...$settings): Factory
    {
        return $this->fromSettings(...$settings);
    }

    /**
     * Use the provided JSON of settings to fill this factory's
     * model class' fillable attributes, to generate any related
     * models with the given attributes and nested relationships,
     * and to call any methods in this factory class with the
     * corresponding values as method arguments.
     *
     * @param string $settings
     *
     * @return Factory
     * @throws JsonException
     * @throws MissingTraitException
     * @throws ReflectionException
     */
    public function fromSettingsJson(string $settings): Factory
    {
        $settings = json_decode($settings, true, 512, JSON_THROW_ON_ERROR);

        return $this->fromSettings(...$settings);
    }

    /**
     * Use the provided settings to fill this factory's
     * model class' fillable attributes, to generate any related
     * models with the given attributes and nested relationships,
     * and to call any methods in this factory class with the
     * corresponding values as method arguments.
     *
     * @param mixed ...$settings
     *
     * @return Factory
     * @throws ReflectionException
     * @throws MissingTraitException
     */
    private function fromSettings(mixed ...$settings): Factory
    {
        $this->parseSettings(...$settings);

        /** @var Factory $this */
        $model = $this->newModel();
        /** @var Model $model */
        return $model::factory()
            // apply the attributes to the state
            ->state($this->attributes)
            // add any relationships to the factory
            ->when(! empty($this->relations), function (self $factory) use ($model) {
                foreach ($this->relations as $relationship => $valuesArray) {
                    // valuesArray might be a nested array if it's one of many, or it might just be an array of values
                    if (! is_array(array_values($valuesArray)[0])) {
                        // for consistency, make the array of values into a nested array
                        $valuesArray = [$valuesArray];
                    }

                    foreach ($valuesArray as $values) {
                        // get the related class, so we can call its factory and chain its fromSettings
                        $relationClass = get_class($model->$relationship()->getRelated());

                        if (! method_exists(($relationClass)::factory(), "fromSettings")) {
                            throw new MissingTraitException(sprintf("%s must use the FromSettings trait", $relationClass));
                        }

                        // determine the type of relationship
                        $relationFn = Str::startsWith(class_basename($model->$relationship()), "Has") ? "has" : "for";

                        $factory = $factory->$relationFn(($relationClass)::factory()->fromSettingsArray(...$values), $relationship);
                    }
                }

                return $factory;
            })
            // add any customMethods to the factory
            ->when(! empty($this->customMethods), function (self $factory) {
                foreach ($this->customMethods as $method => $valuesArray) {
                    // valuesArray might be a nested array if it's one of many, or it might just be an array of values
                    if (is_array(array_values($valuesArray)[0])) {
                        foreach ($valuesArray as $values) {
                            $factory = $factory->$method(...$values);
                        }
                    } else {
                        $factory = $factory->$method(...$valuesArray);
                    }
                }

                return $factory;
            });
    }

    /**
     * Parse the given settings to populate the
     * attributes and relations arrays.
     *
     * @param ...$settings
     *
     * @return void
     * @throws ReflectionException
     */
    protected function parseSettings(...$settings): void
    {
        // keep a variable for a model instance to check isFillable on each key, without creating a new one each time
        /** @var Factory $this */
        $emptyInstance = $this->newModel();

        // keep a copy of the model's relationships, so we can check if the setting is for a relationship
        $modelRelations = FindRelations::getModelRelations($this);
        // getModelRelations uses reflection and requires return type definitions, so track if there are any results,
        // so we know if we need to fall back to guessing
        $hasModelRelations = $modelRelations->isNotEmpty();

        // keep a copy of any methods in this class, so we can check if the setting is for a custom method in this class
        $factoryMethods = FindMethods::getFactoryMethods($this);

        foreach ($settings as $key => $value) {
            // is it a fillable attribute?
            if ($emptyInstance->isFillable($key)) {
                $this->attributes[$key] = $value;
                continue;
            }

            // is it a relationship?
            if (($hasModelRelations && $modelRelations->contains($key)) || FindRelations::guessModelRelation($this, $key)) {
                $this->relations[$key] = $value;
                continue;
            }

            // is it a custom function?
            if ($factoryMethods->contains($key)) {
                $this->customMethods[$key] = $value;
                continue;
            }
        }
    }
}
