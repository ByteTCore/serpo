<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply WHERE JSON_CONTAINS conditions.
 */
class JsonContainsCriteria extends BaseCriteria
{
    /**
     * Apply the WHERE JSON_CONTAINS condition to the query builder.
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
                fn (string $col) => $q->whereJsonContains($col, $this->value, $this->getBoolean())
            )
        );
    }
}
