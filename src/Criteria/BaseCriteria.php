<?php

namespace ByteTCore\Serpo\Criteria;

use ByteTCore\Serpo\Contracts\CriteriaInterface;
use ByteTCore\Serpo\Exceptions\InvalidCriteriaException;

abstract class BaseCriteria implements CriteriaInterface
{
    /**
     * Create a new criteria instance.
     *
     * @param  mixed  $value  The filtering value from request.
     * @param  array  $config  The criteria configuration.
     */
    public function __construct(
        protected mixed $value,
        protected array $config = []
    ) {
        $this->validateColumns();
    }

    /**
     * Get the raw columns from config.
     */
    protected function getColumns(): string|array
    {
        return $this->config['columns'] ?? '';
    }

    /**
     * Parse columns into an array.
     */
    protected function parseColumns(): array
    {
        $columns = $this->getColumns();

        return is_array($columns)
            ? $columns
            : explode('|', $columns);
    }

    /**
     * Get the boolean connector (and/or).
     */
    protected function getBoolean(): string
    {
        return $this->config['boolean'] ?? 'and';
    }

    /**
     * Get the operator for the condition.
     */
    protected function getOperator(string $default = '='): string
    {
        return $this->config['operator'] ?? $default;
    }

    /**
     * Validate the columns configuration.
     *
     * @throws InvalidCriteriaException
     */
    private function validateColumns(): void
    {
        $columns = $this->getColumns();

        if (is_string($columns)) {
            if (empty(trim($columns)) || str_contains($columns, ' ')) {
                throw InvalidCriteriaException::invalidFormat($columns);
            }

            return;
        }

        if (is_array($columns)) {
            if (empty($columns)) {
                throw InvalidCriteriaException::emptyColumns();
            }

            foreach ($columns as $field) {
                if (! is_string($field) || empty(trim($field))) {
                    throw InvalidCriteriaException::invalidArrayItem();
                }
            }

            return;
        }

        throw InvalidCriteriaException::invalidFormat((string) $columns);
    }
}
