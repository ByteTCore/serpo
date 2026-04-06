<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\WhereCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class WhereCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_applies_equal_by_default(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(function ($arg) {
                return is_callable($arg);
            })
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });

        $query->shouldReceive('where')
            ->once()
            ->with('status', '=', 'active', 'and')
            ->andReturnSelf();

        $criteria = new WhereCriteria('active', ['columns' => 'status']);
        $criteria->apply($query);
    }

    public function test_applies_custom_operator(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(function ($arg) {
                return is_callable($arg);
            })
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });

        $query->shouldReceive('where')
            ->once()
            ->with('age', '>=', 18, 'and')
            ->andReturnSelf();

        $criteria = new WhereCriteria(18, ['columns' => 'age', 'boolean' => 'and', 'operator' => '>=']);
        $criteria->apply($query);
    }

    public function test_applies_to_multiple_columns(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(function ($arg) {
                return is_callable($arg);
            })
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });

        $query->shouldReceive('where')
            ->once()
            ->with('first_name', '=', 'John', 'or')
            ->andReturnSelf();

        $query->shouldReceive('where')
            ->once()
            ->with('last_name', '=', 'John', 'or')
            ->andReturnSelf();

        $criteria = new WhereCriteria('John', ['columns' => 'first_name|last_name', 'boolean' => 'or']);
        $criteria->apply($query);
    }

    public function test_skips_when_value_is_null_or_empty_string(): void
    {
        $query = $this->mockBuilder();
        $query->shouldNotReceive('where');

        $criteria = new WhereCriteria(null, ['columns' => 'status']);
        $criteria->apply($query);
        
        $criteria2 = new WhereCriteria('', ['columns' => 'status']);
        $criteria2->apply($query);
    }

    public function test_does_not_skip_zero(): void
    {
        $query = $this->mockBuilder();
        
        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });
            
        $query->shouldReceive('where')
            ->once()
            ->with('status', '=', 0, 'and')
            ->andReturnSelf();

        $criteria = new WhereCriteria(0, ['columns' => 'status']);
        $criteria->apply($query);
    }
}
