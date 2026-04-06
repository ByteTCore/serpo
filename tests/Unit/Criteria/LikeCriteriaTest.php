<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\LikeCriteria;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class LikeCriteriaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function mockBuilder(): Builder
    {
        return Mockery::mock(Builder::class)->makePartial();
    }

    public function test_applies_contains_pattern_by_default(): void
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
            ->with('name', 'like', '%John%', 'and')
            ->andReturnSelf();

        $criteria = new LikeCriteria('John', ['columns' => 'name']);
        $criteria->apply($query);
    }

    public function test_applies_starts_with_pattern(): void
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
            ->with('name', 'like', 'John%', 'and')
            ->andReturnSelf();

        $criteria = new LikeCriteria('John', ['columns' => 'name', 'boolean' => 'and', 'operator' => 'like', 'pattern' => 'starts_with']);
        $criteria->apply($query);
    }

    public function test_applies_ends_with_pattern(): void
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
            ->with('name', 'like', '%son', 'and')
            ->andReturnSelf();

        $criteria = new LikeCriteria('son', ['columns' => 'name', 'boolean' => 'and', 'operator' => 'like', 'pattern' => 'ends_with']);
        $criteria->apply($query);
    }

    public function test_applies_not_like_operator(): void
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
            ->with('name', 'not like', '%spam%', 'and')
            ->andReturnSelf();

        $criteria = new LikeCriteria('spam', ['columns' => 'name', 'boolean' => 'and', 'operator' => 'not like']);
        $criteria->apply($query);
    }
    
    public function test_skips_when_value_is_null_or_empty(): void
    {
        $query = $this->mockBuilder();
        $query->shouldNotReceive('where');

        $criteria = new LikeCriteria(null, ['columns' => 'name']);
        $criteria->apply($query);
        
        $criteria2 = new LikeCriteria('', ['columns' => 'name']);
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
            
        $query->shouldReceive('where')
            ->once()
            ->with('name', 'like', '%0%', 'and')
            ->andReturnSelf();

        $criteria = new LikeCriteria('0', ['columns' => 'name']);
        $criteria->apply($query);
    }
}
