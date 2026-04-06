<?php

namespace ByteTCore\Serpo\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Criteria to apply LIKE conditions.
 * Supports patterns: contains (default), starts_with, ends_with.
 */
class LikeCriteria extends BaseCriteria
{
    /**
     * Apply the LIKE condition to the query builder.
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
        $operator = $this->getOperator('like');

        $query->where(
            fn (Builder $q) => array_walk(
                $columns,
                fn (string $col) => $q->where($col, $operator, $this->formatValue(), $this->getBoolean())
            )
        );
    }

    /**
     * Format the value according to the configured pattern.
     *
     * @return string
     */
    private function formatValue(): string
    {
        $pattern = $this->config['pattern'] ?? 'contains';

        return match ($pattern) {
            'starts_with' => "{$this->value}%",
            'ends_with' => "%{$this->value}",
            default => "%{$this->value}%",
        };
    }
}
