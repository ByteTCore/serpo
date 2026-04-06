<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\OrderByCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class OrderByCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_applies_order_by_desc(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturnSelf();

        $criteria = new OrderByCriteria('desc', ['columns' => 'created_at']);
        $criteria->apply($query);
    }

    public function test_applies_order_by_asc_by_default_on_invalid_direction(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('orderBy')
            ->once()
            ->with('name', 'asc')
            ->andReturnSelf();

        $criteria = new OrderByCriteria('invalid_direction', ['columns' => 'name']);
        $criteria->apply($query);
    }

    public function test_skips_when_null(): void
    {
        $query = $this->mockBuilder();

        $query->shouldNotReceive('orderBy');

        $criteria = new OrderByCriteria(null, ['columns' => 'name']);
        $criteria->apply($query);
    }
}
