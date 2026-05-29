<?php

namespace ByteTCore\Serpo\Traits;

use ByteTCore\Serpo\Criteria\Condition;
use ByteTCore\Serpo\Criteria\ConditionGroup;

trait HasCriteria
{
    protected array $conditions = [];

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
     * Apply a group of criteria filters to the repository query.
     *
     * Only keys present in {@see conditions()} are processed.
     * Shorthand strings are normalized to ['class' => ...] automatically.
     */
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

        $conditions = $this->conditions();
        $keys = $this->resolveFilterKeys(array_keys($conditions));

        foreach ($keys as $key) {
            if (! array_key_exists($key, $filters)) {
                continue;
            }

            $config = $conditions[$key];
            $value = $filters[$key];

            if ($config instanceof Condition || $config instanceof ConditionGroup) {
                $config->resolve($value, $key)->apply($this->query);

                continue;
            }

            if (is_string($config)) {
                $config = ['class' => $config];
            }

            $config['columns'] ??= $key;
            $class = $config['class'];
            $criteria = new $class($value, $config);

            $criteria->apply($this->query);
        }

        return $this;
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
