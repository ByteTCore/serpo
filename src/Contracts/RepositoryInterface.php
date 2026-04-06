<?php

namespace ByteTCore\Serpo\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
interface RepositoryInterface
{
    /**
     * Apply a group of filters to the repository query.
     *
     * @param array|null $filters
     * @return static
     */
    public function filters(?array $filters = null): static;

    /**
     * Prevent the internal query from being reset after execution.
     *
     * @return static
     */
    public function withoutAutoReset(): static;

    /**
     * Manually reset the internal query builder.
     *
     * @return static
     */
    public function resetQuery(): static;
}
