<?php

namespace Brandonkerr\EloquentFromSettings\Contracts;

interface FromSettingsInterface
{
    /**
     * Get whether this class will throw a MissingTraitException if a keyed relationship does not use
     * the FromSettings trait
     *
     * @return bool
     */
    public function getThrowsMissingTraitException(): bool;

    /**
     * Get whether this class will throw an UnknownKeyException if a key cannot be matched to an
     * attribute, relationship, or function
     *
     * @return bool
     */
    public function getThrowsUnknownKeyException(): bool;
}
