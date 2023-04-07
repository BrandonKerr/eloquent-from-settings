<?php

namespace Brandonkerr\EloquentFromSettings\Helpers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionClass;

class FindMethods
{
    /**
     * Get the methods for the given factory class.
     *
     * @param Factory<Model> $factory
     * @return Collection<string, mixed>
     */
    public static function getFactoryMethods(Factory $factory): Collection
    {
        $reflector = new ReflectionClass($factory);

        return collect($reflector->getMethods())
            ->filter(
                fn ($method) => $method->class === get_class($factory)
            )
            ->pluck("name");
    }
}
