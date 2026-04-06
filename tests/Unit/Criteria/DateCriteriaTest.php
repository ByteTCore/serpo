<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\DateCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class DateCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_applies_date_equal_by_default(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });

        $query->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '=', '2025-01-01', 'and')
            ->andReturnSelf();

        $criteria = new DateCriteria('2025-01-01', ['columns' => 'created_at']);
        $criteria->apply($query);
    }

    public function test_applies_date_greater_equal(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });

        $query->shouldReceive('whereDate')
            ->once()
            ->with('start_date', '>=', '2025-06-01', 'and')
            ->andReturnSelf();

        $criteria = new DateCriteria('2025-06-01', ['columns' => 'start_date', 'boolean' => 'and', 'operator' => '>=']);
        $criteria->apply($query);
    }

    public function test_applies_to_multiple_date_columns(): void
    {
        $query = $this->mockBuilder();

        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });

        $query->shouldReceive('whereDate')
            ->once()
            ->with('start_date', '=', '2025-01-01', 'or')
            ->andReturnSelf();

        $query->shouldReceive('whereDate')
            ->once()
            ->with('end_date', '=', '2025-01-01', 'or')
            ->andReturnSelf();

        $criteria = new DateCriteria('2025-01-01', ['columns' => 'start_date|end_date', 'boolean' => 'or']);
        $criteria->apply($query);
    }
    
    public function test_skips_when_value_is_null_or_empty(): void
    {
        $query = $this->mockBuilder();
        $query->shouldNotReceive('where');

        $criteria = new DateCriteria(null, ['columns' => 'created_at']);
        $criteria->apply($query);
        
        $criteria2 = new DateCriteria('', ['columns' => 'created_at']);
        $criteria2->apply($query);
    }
    
    public function test_does_not_skip_zero_string(): void
    {
        $query = $this->mockBuilder();
        
        $query->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) use ($query) {
                $callback($query);
                return $query;
            });
            
        $query->shouldReceive('whereDate')
            ->once()
            ->with('created_at', '=', '0', 'and')
            ->andReturnSelf();

        $criteria = new DateCriteria('0', ['columns' => 'created_at']);
        $criteria->apply($query);
    }
}
