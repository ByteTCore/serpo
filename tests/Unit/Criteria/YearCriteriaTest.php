<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\YearCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class YearCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_applies_year(): void
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

        $query->shouldReceive('whereYear')
            ->once()
            ->with('created_at', '=', 2025, 'and')
            ->andReturnSelf();

        $criteria = new YearCriteria(2025, ['columns' => 'created_at']);
        $criteria->apply($query);
    }
}
