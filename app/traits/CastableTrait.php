<?php

namespace traits;

trait CastableTrait
{
    public static function cast(array $properties): self
    {
        return new self(...$properties);
    }
}