<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\MonthCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class MonthCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_applies_month(): void
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

        $query->shouldReceive('whereMonth')
            ->once()
            ->with('created_at', '=', 12, 'and')
            ->andReturnSelf();

        $criteria = new MonthCriteria(12, ['columns' => 'created_at']);
        $criteria->apply($query);
    }
}
