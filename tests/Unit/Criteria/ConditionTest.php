<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\Condition;
use ByteTCore\Serpo\Criteria\BaseCriteria;
use ByteTCore\Serpo\Criteria\WhereCriteria;
use ByteTCore\Serpo\Criteria\LikeCriteria;
use ByteTCore\Serpo\Criteria\DateCriteria;
use ByteTCore\Serpo\Criteria\BetweenCriteria;
use ByteTCore\Serpo\Criteria\InCriteria;
use ByteTCore\Serpo\Criteria\NullCriteria;
use ByteTCore\Serpo\Criteria\NotNullCriteria;
use ByteTCore\Serpo\Criteria\JsonContainsCriteria;
use ByteTCore\Serpo\Criteria\OrderByCriteria;
use ByteTCore\Serpo\Criteria\YearCriteria;
use ByteTCore\Serpo\Criteria\MonthCriteria;
use ByteTCore\Serpo\Constants\Filter;
use ByteTCore\Serpo\Exceptions\InvalidCriteriaException;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_where_creates_where_criteria(): void
    {
        $condition = Condition::where('status');

        $criteria = $condition->resolve('active');
        $this->assertInstanceOf(WhereCriteria::class, $criteria);
    }

    public function test_like_creates_like_criteria(): void
    {
        $condition = Condition::like('name');

        $criteria = $condition->resolve('John');
        $this->assertInstanceOf(LikeCriteria::class, $criteria);
    }

    public function test_date_creates_date_criteria(): void
    {
        $condition = Condition::date('created_at');

        $criteria = $condition->resolve('2024-01-01');
        $this->assertInstanceOf(DateCriteria::class, $criteria);
    }

    public function test_between_creates_between_criteria(): void
    {
        $condition = Condition::between('price');

        $criteria = $condition->resolve([100, 500]);
        $this->assertInstanceOf(BetweenCriteria::class, $criteria);
    }

    public function test_where_in_creates_in_criteria(): void
    {
        $condition = Condition::whereIn('tags');

        $criteria = $condition->resolve(['php', 'laravel']);
        $this->assertInstanceOf(InCriteria::class, $criteria);
    }

    public function test_where_null_creates_null_criteria(): void
    {
        $condition = Condition::whereNull('deleted_at');

        $criteria = $condition->resolve(true);
        $this->assertInstanceOf(NullCriteria::class, $criteria);
    }

    public function test_where_not_null_creates_not_null_criteria(): void
    {
        $condition = Condition::whereNotNull('email_verified_at');

        $criteria = $condition->resolve(true);
        $this->assertInstanceOf(NotNullCriteria::class, $criteria);
    }

    public function test_json_contains_creates_json_criteria(): void
    {
        $condition = Condition::jsonContains('tags');

        $criteria = $condition->resolve('php');
        $this->assertInstanceOf(JsonContainsCriteria::class, $criteria);
    }

    public function test_order_by_creates_order_criteria(): void
    {
        $condition = Condition::orderBy('created_at');

        $criteria = $condition->resolve('desc');
        $this->assertInstanceOf(OrderByCriteria::class, $criteria);
    }

    public function test_year_creates_year_criteria(): void
    {
        $condition = Condition::year('birth_date');

        $criteria = $condition->resolve(1990);
        $this->assertInstanceOf(YearCriteria::class, $criteria);
    }

    public function test_month_creates_month_criteria(): void
    {
        $condition = Condition::month('birth_date');

        $criteria = $condition->resolve(6);
        $this->assertInstanceOf(MonthCriteria::class, $criteria);
    }

    public function test_custom_creates_criteria_from_class_name(): void
    {
        $condition = Condition::custom(WhereCriteria::class);

        $criteria = $condition->resolve('active');
        $this->assertInstanceOf(WhereCriteria::class, $criteria);
    }

    public function test_default_column_from_resolve_key(): void
    {
        $condition = Condition::where();

        $query = Mockery::mock(Builder::class)->makePartial();
        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });
        $query->shouldReceive('where')
            ->once()
            ->with('status', '=', 'active', 'and')
            ->andReturnSelf();

        $criteria = $condition->resolve('active', 'status');
        $criteria->apply($query);
    }

    public function test_fluent_operator_methods(): void
    {
        $query = Mockery::mock(Builder::class)->makePartial();
        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });
        $query->shouldReceive('where')
            ->once()
            ->with('age', '>=', 18, 'and')
            ->andReturnSelf();

        $condition = Condition::where('age')->gte();
        $criteria = $condition->resolve(18);
        $criteria->apply($query);
    }

    public function test_fluent_or_boolean(): void
    {
        $query = Mockery::mock(Builder::class)->makePartial();
        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });
        $query->shouldReceive('where')
            ->once()
            ->with('name', 'like', '%test%', 'or')
            ->andReturnSelf();
        $query->shouldReceive('where')
            ->once()
            ->with('email', 'like', '%test%', 'or')
            ->andReturnSelf();

        $condition = Condition::like('name', 'email')->or();
        $criteria = $condition->resolve('test');
        $criteria->apply($query);
    }

    public function test_fluent_pattern_methods(): void
    {
        $query = Mockery::mock(Builder::class)->makePartial();
        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });
        $query->shouldReceive('where')
            ->once()
            ->with('name', 'like', 'John%', 'and')
            ->andReturnSelf();

        $condition = Condition::like('name')->startsWith();
        $criteria = $condition->resolve('John');
        $criteria->apply($query);
    }

    public function test_order_by_with_direction_shortcut(): void
    {
        $condition = Condition::orderBy('created_at')->asc();
        $query = Mockery::mock(Builder::class)->makePartial();
        $query->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'asc')
            ->andReturnSelf();

        $criteria = $condition->resolve('asc');
        $criteria->apply($query);
    }

    public function test_multiple_factory_methods_accept_variadic_columns(): void
    {
        $condition = Condition::where('first_name', 'last_name', 'email');

        $query = Mockery::mock(Builder::class)->makePartial();
        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });
        $query->shouldReceive('where')->times(3)->andReturnSelf();

        $criteria = $condition->resolve('John');
        $criteria->apply($query);
    }

    public function test_columns_fluent_overrides(): void
    {
        $condition = Condition::where('foo')->columns('status');

        $query = Mockery::mock(Builder::class)->makePartial();
        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });
        $query->shouldReceive('where')
            ->once()
            ->with('status', '=', 'active', 'and')
            ->andReturnSelf();

        $criteria = $condition->resolve('active');
        $criteria->apply($query);
    }

    public function test_or_group_creates_nested_where_with_or(): void
    {
        $group = Condition::orGroup(
            Condition::like('name'),
            Condition::where('email'),
        );

        $outer = Mockery::mock(Builder::class)->makePartial();
        $inner = Mockery::mock(Builder::class)->makePartial();

        $outer->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($inner, $outer) {
                $callback($inner);

                return $outer;
            });

        // Inner query: like condition
        $inner->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($inner) {
                $callback($inner);

                return $inner;
            });
        $inner->shouldReceive('where')
            ->once()
            ->with('name', 'like', '%test%', 'and')
            ->andReturnSelf();

        // Inner query: where condition
        $inner->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($inner) {
                $callback($inner);

                return $inner;
            });
        $inner->shouldReceive('where')
            ->once()
            ->with('email', '=', 'test', 'and')
            ->andReturnSelf();

        $criteria = $group->resolve('test');
        $criteria->apply($outer);
    }

    public function test_and_group_creates_nested_where_with_and(): void
    {
        $group = Condition::andGroup(
            Condition::date('created_at')->gte(),
            Condition::date('created_at')->lte(),
        );

        $outer = Mockery::mock(Builder::class)->makePartial();
        $inner = Mockery::mock(Builder::class)->makePartial();

        $outer->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($inner, $outer) {
                $callback($inner);

                return $outer;
            });

        // Inner: date >=
        $inner->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($inner) {
                $callback($inner);

                return $inner;
            });
        $inner->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '>=', '2024-01-01', 'and')
            ->andReturnSelf();

        // Inner: date <=
        $inner->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($inner) {
                $callback($inner);

                return $inner;
            });
        $inner->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '<=', '2024-01-01', 'and')
            ->andReturnSelf();

        $criteria = $group->resolve('2024-01-01');
        $criteria->apply($outer);
    }
}
