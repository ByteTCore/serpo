<?php

namespace ByteTCore\Serpo\Criteria;

use ByteTCore\Serpo\Constants\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply LIKE conditions.
 * Supports patterns: contains (default), starts_with, ends_with.
 */
class LikeCriteria extends BaseCriteria
{
    /**
     * Apply the LIKE condition to the query builder.
     */
    public function apply(Builder $query): void
    {
        if ($this->value === null || $this->value === '') {
            return;
        }

        $columns = $this->parseColumns();
        $operator = $this->getOperator(Filter::LIKE);

        $query->where(
            fn (Builder $q) => array_walk(
                $columns,
                fn (string $col) => $q->where($col, $operator, $this->formatValue(), $this->getBoolean())
            )
        );
    }

    /**
     * Format the value according to the configured pattern.
     */
    private function formatValue(): string
    {
        $pattern = $this->config['pattern'] ?? Filter::CONTAINS;

        return match ($pattern) {
            Filter::STARTS_WITH => "{$this->value}%",
            Filter::ENDS_WITH => "%{$this->value}",
            default => "%{$this->value}%",
        };
    }
}
