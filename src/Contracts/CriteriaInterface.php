<?php

namespace ByteTCore\Serpo\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface CriteriaInterface
{
    /**
     * @param  mixed  $value  The value given by the payload/request to filter by
     * @param  array  $config  The configuration constraints set in the repository
     */
    public function __construct(mixed $value, array $config = []);

    /**
     * Apply the given conditions to the Eloquent query builder.
     */
    public function apply(Builder $query): void;
}
