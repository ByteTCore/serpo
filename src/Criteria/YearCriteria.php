<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply year conditions using whereYear.
 */
class YearCriteria extends BaseCriteria
{
    /**
     * Apply the year condition to the query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
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
                fn (string $col) => $q->whereYear($col, $operator, $this->value, $this->getBoolean())
            )
        );
    }
}
