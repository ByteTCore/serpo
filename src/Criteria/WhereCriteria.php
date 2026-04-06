<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply simple where conditions.
 * Supports custom operators and boolean logical operators.
 */
class WhereCriteria extends BaseCriteria
{
    /**
     * Apply the where condition to the query builder.
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
                fn (string $col) => $q->where($col, $operator, $this->value, $this->getBoolean())
            )
        );
    }
}
