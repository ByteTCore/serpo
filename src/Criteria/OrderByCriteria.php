<?php

namespace ByteTCore\Serpo\Criteria;

use ByteTCore\Serpo\Constants\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply dynamic ORDER BY conditions.
 */
class OrderByCriteria extends BaseCriteria
{
    /**
     * Apply the order by condition to the query builder.
     */
    public function apply(Builder $query): void
    {
        if ($this->value === null || $this->value === '') {
            return;
        }

        $columns = $this->parseColumns();

        // Ensure direction is valid
        $direction = strtolower((string) $this->value);
        if (! in_array($direction, [Filter::ASC, Filter::DESC], true)) {
            $direction = Filter::ASC;
        }

        array_walk(
            $columns,
            fn (string $col) => $query->orderBy($col, $direction)
        );
    }
}
