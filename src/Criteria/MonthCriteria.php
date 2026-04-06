<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply month conditions using whereMonth.
 */
class MonthCriteria extends BaseCriteria
{
    /**
     * Apply the month condition to the query builder.
     */
    public function apply(Builder $query): void
    {
        if ($this->value === null || $this->value === '') {
            return;
        }

        $columns = $this->parseColumns();
        $operator = $this->getOperator('=');

        $query->where(
            fn (Builder $q) => array_walk(
                $columns,
                fn (string $col) => $q->whereMonth($col, $operator, $this->value, $this->getBoolean())
            )
        );
    }
}
