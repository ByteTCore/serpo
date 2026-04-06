<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\BetweenCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class BetweenCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_applies_between(): void
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

        $query->shouldReceive('whereBetween')
            ->once()
            ->with('price', [100, 500], 'and')
            ->andReturnSelf();

        $criteria = new BetweenCriteria([100, 500], ['columns' => 'price']);
        $criteria->apply($query);
    }

    public function test_skips_when_not_array(): void
    {
        $query = $this->mockBuilder();

        $query->shouldNotReceive('where');
        $query->shouldNotReceive('whereBetween');

        $criteria = new BetweenCriteria(100, ['columns' => 'price']);
        $criteria->apply($query);
    }
}
