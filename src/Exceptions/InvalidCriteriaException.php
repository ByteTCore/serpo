<?php

namespace ByteTCore\Serpo\Exceptions;

use InvalidArgumentException;

class InvalidCriteriaException extends InvalidArgumentException
{
    public static function invalidFormat(mixed $columns): self
    {
        $type = is_object($columns) ? $columns::class : gettype($columns);

        return new self("Columns must be a non-empty string or array of strings, got: {$type}");
    }

    public static function emptyColumns(): self
    {
        return new self('Columns cannot be empty.');
    }

    public static function invalidArrayItem(): self
    {
        return new self('All columns must be non-empty strings.');
    }
}
