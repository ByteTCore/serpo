<?php

namespace ByteTCore\Serpo\Traits;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Traits\ForwardsCalls;

trait DelegatesToBuilder
{
    use ForwardsCalls;

    /**
     * Forward unknown method calls to the Eloquent query builder.
     *
     * - Methods returning Builder (where, orderBy, ...) update the internal query and return $this.
     * - Methods returning results (get, first, count, ...) return the result and auto-reset the query.
     */
    public function __call(string $method, array $parameters): mixed
    {
        $cached = method_exists($this, 'getCachedResult')
            ? $this->getCachedResult($method, $parameters)
            : false;

        if ($cached !== false) {
            return $cached;
        }

        try {
            $result = $this->forwardCallTo($this->query, $method, $parameters);
        } catch (BadMethodCallException) {
            $result = $this->forwardCallTo($this->model->newModelQuery(), $method, $parameters);
        }

        if ($result instanceof Builder) {
            $this->query = $result;

            return $this;
        }

        if ($this->autoReset) {
            $this->resetQuery();
        }

        if (method_exists($this, 'putCacheResult')) {
            $result = $this->putCacheResult($result, $method, $parameters);
        }

        return $result;
    }
}
