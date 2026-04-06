<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply WHERE JSON_DOESNT_CONTAIN conditions.
 */
class JsonNotContainsCriteria extends BaseCriteria
{
    /**
     * Apply the WHERE JSON_DOESNT_CONTAIN condition to the query builder.
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

        $query->where(
            fn (Builder $q) => array_walk(
                $columns,
                fn (string $col) => $q->whereJsonDoesntContain($col, $this->value, $this->getBoolean())
            )
        );
    }
}
