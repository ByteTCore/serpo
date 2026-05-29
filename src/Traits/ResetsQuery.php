<?php

namespace ByteTCore\Serpo\Traits;

trait ResetsQuery
{
    protected bool $autoReset = true;

    /**
     * Disable auto-reset so the current query can be reused across multiple calls.
     */
    public function withoutAutoReset(): static
    {
        $this->autoReset = false;

        return $this;
    }

    /**
     * Re-create the query builder from the model, clearing all applied conditions.
     */
    public function resetQuery(): static
    {
        $this->query = $this->model->newQuery();

        return $this;
    }
}
