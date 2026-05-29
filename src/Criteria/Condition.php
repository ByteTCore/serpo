<?php

namespace ByteTCore\Serpo\Criteria;

use ByteTCore\Serpo\Constants\Filter;

class Condition
{
    /**
     * @param class-string<BaseCriteria> $criteriaClass
     */
    private function __construct(
        private readonly string $criteriaClass,
        private array           $config = [],
    )
    {
    }

    // ── Factory methods ────────────────────────────────────────────────

    public static function where(string ...$columns): self
    {
        return new self(WhereCriteria::class, ['columns' => $columns]);
    }

    public static function like(string ...$columns): self
    {
        return new self(LikeCriteria::class, ['columns' => $columns]);
    }

    public static function date(string ...$columns): self
    {
        return new self(DateCriteria::class, ['columns' => $columns]);
    }

    public static function between(string ...$columns): self
    {
        return new self(BetweenCriteria::class, ['columns' => $columns]);
    }

    public static function notBetween(string ...$columns): self
    {
        return new self(NotBetweenCriteria::class, ['columns' => $columns]);
    }

    public static function whereIn(string ...$columns): self
    {
        return new self(InCriteria::class, ['columns' => $columns]);
    }

    public static function whereNotIn(string ...$columns): self
    {
        return new self(NotInCriteria::class, ['columns' => $columns]);
    }

    public static function whereNull(string ...$columns): self
    {
        return new self(NullCriteria::class, ['columns' => $columns]);
    }

    public static function whereNotNull(string ...$columns): self
    {
        return new self(NotNullCriteria::class, ['columns' => $columns]);
    }

    public static function jsonContains(string ...$columns): self
    {
        return new self(JsonContainsCriteria::class, ['columns' => $columns]);
    }

    public static function jsonNotContains(string ...$columns): self
    {
        return new self(JsonNotContainsCriteria::class, ['columns' => $columns]);
    }

    public static function orderBy(string ...$columns): self
    {
        return new self(OrderByCriteria::class, ['columns' => $columns]);
    }

    public static function year(string ...$columns): self
    {
        return new self(YearCriteria::class, ['columns' => $columns]);
    }

    public static function month(string ...$columns): self
    {
        return new self(MonthCriteria::class, ['columns' => $columns]);
    }

    /**
     * @param class-string<BaseCriteria> $class
     */
    public static function custom(string $class): self
    {
        return new self($class);
    }

    // ── Fluent config methods ──────────────────────────────────────────

    public function columns(string ...$columns): self
    {
        $this->config['columns'] = $columns;

        return $this;
    }

    public function operator(string $operator): self
    {
        $this->config['operator'] = $operator;

        return $this;
    }

    public function gt(): self
    {
        return $this->operator(Filter::GT);
    }

    public function gte(): self
    {
        return $this->operator(Filter::GTE);
    }

    public function lt(): self
    {
        return $this->operator(Filter::LT);
    }

    public function lte(): self
    {
        return $this->operator(Filter::LTE);
    }

    public function not(): self
    {
        return $this->operator(Filter::NOT_EQUAL);
    }

    public function boolean(string $boolean): self
    {
        $this->config['boolean'] = $boolean;

        return $this;
    }

    public function and(): self
    {
        return $this->boolean(Filter::AND);
    }

    public function or(): self
    {
        return $this->boolean(Filter::OR);
    }

    public function pattern(string $pattern): self
    {
        $this->config['pattern'] = $pattern;

        return $this;
    }

    public function contains(): self
    {
        return $this->pattern(Filter::CONTAINS);
    }

    public function startsWith(): self
    {
        return $this->pattern(Filter::STARTS_WITH);
    }

    public function endsWith(): self
    {
        return $this->pattern(Filter::ENDS_WITH);
    }

    public function asc(): self
    {
        $this->config['direction'] = Filter::ASC;

        return $this;
    }

    public function desc(): self
    {
        $this->config['direction'] = Filter::DESC;

        return $this;
    }

        // ── Group factories ────────────────────────────────────────────────

    public static function orGroup(self ...$conditions): ConditionGroup
    {
        return new ConditionGroup($conditions, Filter::OR);
    }

    public static function andGroup(self ...$conditions): ConditionGroup
    {
        return new ConditionGroup($conditions, Filter::AND);
    }

    // ── Resolution ─────────────────────────────────────────────────────

    /**
     * Create the actual criteria instance from this blueprint.
     */
    public function resolve(mixed $value, ?string $defaultColumn = null): BaseCriteria
    {
        $config = $this->config;

        if (empty($config['columns']) && $defaultColumn !== null) {
            $config['columns'] = [$defaultColumn];
        }

        $class = $this->criteriaClass;

        return new $class($value, $config);
    }
}
