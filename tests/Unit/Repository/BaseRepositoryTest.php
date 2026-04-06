<?php

namespace ByteTCore\Serpo\Tests\Unit\Repository;

use ByteTCore\Serpo\Criteria\LikeCriteria;
use ByteTCore\Serpo\Criteria\WhereCriteria;
use ByteTCore\Serpo\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class BaseRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Model $model;

    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = Mockery::mock(Builder::class);
        $this->model = Mockery::mock(Model::class);
        $this->model->shouldReceive('newQuery')->andReturn($this->builder);
    }

    private function makeRepository(?array $conditions = []): BaseRepository
    {
        $model = $this->model;

        return new class($model, $conditions) extends BaseRepository
        {
            public function __construct(Model $model, private array $customConditions = [])
            {
                parent::__construct($model);
            }

            protected array $conditions = [];

            public function initConditions(): void
            {
                $this->conditions = $this->customConditions;
            }
        };
    }

    private function makeRepositoryWithConditions(array $conditions): BaseRepository
    {
        $model = $this->model;

        return new class($model, $conditions) extends BaseRepository
        {
            public function __construct(Model $model, array $conditions)
            {
                $this->conditions = $conditions;
                parent::__construct($model);
            }
        };
    }

    public function test_filters_returns_self_on_null(): void
    {
        $repo = $this->makeRepository();

        $result = $repo->filters(null);

        $this->assertSame($repo, $result);
    }

    public function test_filters_skips_unknown_keys(): void
    {
        $repo = $this->makeRepositoryWithConditions([
            'status' => ['class' => WhereCriteria::class],
        ]);

        $this->builder->shouldReceive('when')->never();

        $result = $repo->filters(['unknown_key' => 'value']);

        $this->assertSame($repo, $result);
    }

    public function test_filters_applies_matching_criteria(): void
    {
        $repo = $this->makeRepositoryWithConditions([
            'status' => ['class' => WhereCriteria::class],
        ]);

        $this->builder->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) {
                $callback($this->builder);

                return $this->builder;
            });

        $this->builder->shouldReceive('where')
            ->once()
            ->with('status', '=', 'active', 'and')
            ->andReturnSelf();

        $result = $repo->filters(['status' => 'active']);

        $this->assertSame($repo, $result);
    }

    public function test_filters_uses_custom_columns_and_operator(): void
    {
        $repo = $this->makeRepositoryWithConditions([
            'keyword' => [
                'class' => LikeCriteria::class,
                'columns' => 'name|email',
                'boolean' => 'or',
            ],
        ]);

        $this->builder->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) {
                $callback($this->builder);

                return $this->builder;
            });

        $this->builder->shouldReceive('where')
            ->once()
            ->with('name', 'like', '%test%', 'or')
            ->andReturnSelf();

        $this->builder->shouldReceive('where')
            ->once()
            ->with('email', 'like', '%test%', 'or')
            ->andReturnSelf();

        $repo->filters(['keyword' => 'test']);
    }

    public function test_filters_passes_extra_params(): void
    {
        $repo = $this->makeRepositoryWithConditions([
            'min_age' => [
                'class' => WhereCriteria::class,
                'columns' => 'age',
                'operator' => '>=',
            ],
        ]);

        $this->builder->shouldReceive('where')
            ->once()
            ->withArgs(fn ($arg) => is_callable($arg))
            ->andReturnUsing(function ($callback) {
                $callback($this->builder);

                return $this->builder;
            });

        $this->builder->shouldReceive('where')
            ->once()
            ->with('age', '>=', 18, 'and')
            ->andReturnSelf();

        $repo->filters(['min_age' => 18]);
    }

    public function test_without_auto_reset_keeps_query(): void
    {
        $repo = $this->makeRepository();

        $this->builder->shouldReceive('where')
            ->once()
            ->with('active', true)
            ->andReturnSelf();

        $this->builder->shouldReceive('get')
            ->twice()
            ->with()
            ->andReturn(new Collection);

        // Without auto-reset — model->newQuery should not be called again
        $this->model->shouldReceive('newQuery')->andReturn($this->builder);

        $repo->withoutAutoReset();
        $repo->where('active', true);
        $repo->get();
        $repo->get(); // same query should still work
    }

    public function test_call_forwards_to_query_builder(): void
    {
        $this->builder->shouldReceive('where')
            ->once()
            ->with('name', 'John')
            ->andReturn($this->builder);

        $repo = $this->makeRepository();
        $result = $repo->where('name', 'John');

        $this->assertSame($repo, $result);
    }

    public function test_call_returns_raw_results_for_non_builder(): void
    {
        $this->builder->shouldReceive('count')
            ->once()
            ->andReturn(42);

        $repo = $this->makeRepository();
        $result = $repo->count();

        $this->assertSame(42, $result);
    }
}
