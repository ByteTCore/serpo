<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply WHERE BETWEEN conditions.
 */
class BetweenCriteria extends BaseCriteria
{
    /**
     * Apply the WHERE BETWEEN condition to the query builder.
     */
    public function apply(Builder $query): void
    {
        if (! is_array($this->value) || count($this->value) !== 2) {
            return;
        }

        $columns = $this->parseColumns();

        $query->where(
            fn (Builder $q) => array_walk(
                $columns,
                fn (string $col) => $q->whereBetween($col, $this->value, $this->getBoolean())
            )
        );
    }
}
