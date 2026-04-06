<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\JsonContainsCriteria;
use ByteTCore\Serpo\Criteria\JsonNotContainsCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class JsonCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_json_contains_criteria(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });

        $query->shouldReceive('whereJsonContains')
            ->once()
            ->with('tags', 'laravel', 'and')
            ->andReturnSelf();

        $criteria = new JsonContainsCriteria('laravel', ['columns' => 'tags']);
        $criteria->apply($query);
    }

    public function test_json_not_contains_criteria(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);

                return $query;
            });

        $query->shouldReceive('whereJsonDoesntContain')
            ->once()
            ->with('tags', 'deprecated', 'and')
            ->andReturnSelf();

        $criteria = new JsonNotContainsCriteria('deprecated', ['columns' => 'tags']);
        $criteria->apply($query);
    }
}
