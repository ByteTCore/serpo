<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\NotNullCriteria;
use ByteTCore\Serpo\Criteria\NullCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class NullCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_null_criteria_calls_where_null(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });

        $query->shouldReceive('whereNull')
            ->once()
            ->with('deleted_at', 'and')
            ->andReturnSelf();

        $criteria = new NullCriteria(true, ['columns' => 'deleted_at']);
        $criteria->apply($query);
    }

    public function test_not_null_criteria_calls_where_not_null(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });

        $query->shouldReceive('whereNotNull')
            ->once()
            ->with('email_verified_at', 'and')
            ->andReturnSelf();

        $criteria = new NotNullCriteria(true, ['columns' => 'email_verified_at']);
        $criteria->apply($query);
    }

    public function test_null_criteria_with_multiple_columns(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });

        $query->shouldReceive('whereNull')
            ->once()
            ->with('deleted_at', 'or')
            ->andReturnSelf();

        $query->shouldReceive('whereNull')
            ->once()
            ->with('banned_at', 'or')
            ->andReturnSelf();

        $criteria = new NullCriteria(true, ['columns' => ['deleted_at', 'banned_at'], 'boolean' => 'or']);
        $criteria->apply($query);
    }
}
