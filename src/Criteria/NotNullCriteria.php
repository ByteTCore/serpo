<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply WHERE NOT NULL conditions.
 */
class NotNullCriteria extends BaseCriteria
{
    /**
     * Apply the WHERE NOT NULL condition to the query builder.
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
                fn (string $col) => $q->whereNotNull($col, $this->getBoolean())
            )
        );
    }
}
