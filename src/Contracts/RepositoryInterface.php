<?php

namespace ByteTCore\Serpo\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 */
interface RepositoryInterface
{
    /**
     * Apply a group of filters to the repository query.
     */
    public function filters(?array $filters = null): static;

    /**
     * Prevent the internal query from being reset after execution.
     */
    public function withoutAutoReset(): static;

    /**
     * Manually reset the internal query builder.
     */
    public function resetQuery(): static;
}
