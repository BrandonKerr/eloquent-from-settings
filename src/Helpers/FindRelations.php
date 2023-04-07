<?php

namespace Brandonkerr\EloquentFromSettings\Helpers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

class FindRelations
{
    /**
     * Get the defined relationships for this model.
     * Credit to neeravp from
     * https://laracasts.com/discuss/channels/eloquent/is-there-a-way-to-list-all-relationships-of-a-model?page=1&replyId=765124
     *
     * @param Factory<Model> $factory
     * @return Collection<string, mixed>
     * @throws ReflectionException
     */
    public static function getModelRelations(Factory $factory): Collection
    {
        $reflector = new ReflectionClass($factory->modelName());

        return collect($reflector->getMethods())
            ->filter(
                fn ($method) => ! empty($method->getReturnType()) &&
                    str_contains(
                        $method->getReturnType(),
                        "Illuminate\Database\Eloquent\Relations"
                    )
            )
            ->pluck("name");
    }

    /**
     * Attempt to guess if the model has a relationship for the given related value.
     *
     * @param Factory<Model> $factory
     * @param string $related
     *
     * @return bool
     */
    public static function guessModelRelation(Factory $factory, string $related): bool
    {
        $guess = Str::camel(Str::plural(class_basename($related)));

        return method_exists($factory->modelName(), $guess);
    }
}
