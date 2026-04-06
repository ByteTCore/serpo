<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply WHERE NOT BETWEEN conditions.
 */
class NotBetweenCriteria extends BaseCriteria
{
    /**
     * Apply the WHERE NOT BETWEEN condition to the query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
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
                fn (string $col) => $q->whereNotBetween($col, $this->value, $this->getBoolean())
            )
        );
    }
}
