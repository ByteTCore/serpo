<?php

namespace ByteTCore\Serpo\Traits;

use ByteTCore\Serpo\Criteria\Condition;
use ByteTCore\Serpo\Criteria\ConditionGroup;

trait HasCriteria
{
    protected array $conditions = [];

    protected ?array $appliedFilters = null;

    /**
     * Return the conditions map.
     *
     * Override this method to build conditions dynamically (e.g. with Condition objects).
     */
    protected function conditions(): array
    {
        return $this->conditions;
    }

    /**
     * Return the order in which filter keys should be applied.
     *
     * Override to control application order independently of {@see conditions()}.
     * Keys not listed here are appended in their original order.
     */
    protected function filterOrder(): array
    {
        return [];
    }

    /**
     * Apply a group of criteria filters to the repository query.
     *
     * Filters are applied in the order defined by {@see filterOrder()},
     * falling back to the order of {@see conditions()}.
     */
    public function filters(?array $filters = null): static
    {
        if ($filters === null) {
            return $this;
        }

        $this->appliedFilters = $filters;

        $conditions = $this->conditions();
        $keys = $this->resolveFilterKeys(array_keys($conditions));

        foreach ($keys as $key) {
            if (! array_key_exists($key, $filters)) {
                continue;
            }

            $this->applyFilter($conditions[$key], $filters[$key], $key);
        }

        return $this;
    }

    /**
     * Dispatch a single filter to the appropriate handler.
     *
     * Routes Condition/ConditionGroup instances to their resolve+apply flow,
     * and legacy array/string configs to applyCriteriaConfig().
     */
    private function applyFilter(mixed $config, mixed $value, string $key): void
    {
        if ($config instanceof Condition || $config instanceof ConditionGroup) {
            $config->resolve($value, $key)->apply($this->query);

            return;
        }

        $this->applyCriteriaConfig(
            is_string($config) ? ['class' => $config] : $config,
            $value,
            $key
        );
    }

    /**
     * Instantiate a criteria class from an array config and apply it to the query.
     */
    private function applyCriteriaConfig(array $config, mixed $value, string $key): void
    {
        $config['columns'] ??= $key;

        $criteria = new $config['class']($value, $config);
        $criteria->apply($this->query);
    }

    /**
     * Merge {@see filterOrder()} with remaining condition keys.
     */
    private function resolveFilterKeys(array $conditionKeys): array
    {
        $ordered = $this->filterOrder();

        return array_unique([...$ordered, ...$conditionKeys]);
    }
}
