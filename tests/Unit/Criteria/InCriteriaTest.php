<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\InCriteria;
use ByteTCore\Serpo\Criteria\NotInCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class InCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_in_criteria_calls_where_in(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });

        $query->shouldReceive('whereIn')
            ->once()
            ->with('status', ['active', 'pending'], 'and')
            ->andReturnSelf();

        $criteria = new InCriteria(['active', 'pending'], ['columns' => 'status']);
        $criteria->apply($query);
    }

    public function test_not_in_criteria_calls_where_not_in(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });

        $query->shouldReceive('whereNotIn')
            ->once()
            ->with('status', ['banned', 'deleted'], 'and')
            ->andReturnSelf();

        $criteria = new NotInCriteria(['banned', 'deleted'], ['columns' => 'status']);
        $criteria->apply($query);
    }
}
