<?php

namespace ByteTCore\Serpo\Repositories;

use BadMethodCallException;
use ByteTCore\Serpo\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin Builder
 *
 * @method static orderBy(string|array $column, string $direction = 'asc')
 * @method static groupBy(array|string ...$groups)
 */
abstract class BaseRepository implements RepositoryInterface
{
    use ForwardsCalls;

    /**
     * The Eloquent query builder instance.
     *
     * @var Builder
     */
    protected Builder $query;

    /**
     * Whether to reset the query after the result is returned.
     *
     * @var bool
     */
    protected bool $autoReset = true;

    /**
     * The configuration for mapping request keys to specific criteria class.
     *
     * @var array<string, array<string, mixed>|string>
     */
    protected array $conditions = [];

    /**
     * Create a new repository instance.
     *
     * @param Model $model The Eloquent model instance.
     */
    public function __construct(protected Model $model)
    {
        $this->resetQuery();
    }



    /**
     * Multi-criteria matching with unified config from constants.
     *
     * @param array|null $filters The filters to apply.
     * @return static
     */
    public function filters(?array $filters = null): static
    {
        if ($filters === null) {
            return $this;
        }

        foreach ($filters as $key => $value) {
            if (! isset($this->conditions[$key])) {
                continue;
            }

            $config = $this->conditions[$key];

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

    // ── Query Control ────────────────────────────────────────────────

    /**
     * Disable query builder reset for next chain.
     *
     * @return static
     */
    public function withoutAutoReset(): static
    {
        $this->autoReset = false;

        return $this;
    }

    /**
     * Handle dynamic method calls into the model or query builder.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters): mixed
    {
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

        return $result;
    }

    // ── Internal ─────────────────────────────────────────────────────

    /**
     * Reset the query builder to a new model query.
     *
     * @return static
     */
    public function resetQuery(): static
    {
        $this->query = $this->model->newQuery();

        return $this;
    }
}
