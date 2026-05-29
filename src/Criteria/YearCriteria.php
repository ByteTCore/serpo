<?php

namespace ByteTCore\Serpo\Criteria;

use ByteTCore\Serpo\Constants\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply year conditions using whereYear.
 */
class YearCriteria extends BaseCriteria
{
    /**
     * Apply the year condition to the query builder.
     */
    public function apply(Builder $query): void
    {
        if ($this->value === null || $this->value === '') {
            return;
        }

        $columns = $this->parseColumns();
        $operator = $this->getOperator(Filter::EQUAL);

        $query->where(
            fn (Builder $q) => array_walk(
                $columns,
                fn (string $col) => $q->whereYear($col, $operator, $this->value, $this->getBoolean())
            )
        );
    }
}
