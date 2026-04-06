<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply dynamic ORDER BY conditions.
 */
class OrderByCriteria extends BaseCriteria
{
    /**
     * Apply the order by condition to the query builder.
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
        
        // Ensure direction is valid
        $direction = strtolower((string) $this->value);
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        array_walk(
            $columns, 
            fn (string $col) => $query->orderBy($col, $direction)
        );
    }
}
