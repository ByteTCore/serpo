<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply WHERE NULL conditions.
 */
class NullCriteria extends BaseCriteria
{
    /**
     * Apply the WHERE NULL condition to the query builder.
     */
    public function apply(Builder $query): void
    {
        if ($this->value === null || $this->value === '') {
            return;
        }

        $columns = $this->parseColumns();

        $query->where(
            fn (Builder $q) => array_walk(
                $columns,
                fn (string $col) => $q->whereNull($col, $this->getBoolean())
            )
        );
    }
}
