<?php

namespace ByteTCore\Serpo\Repositories;

use ByteTCore\Serpo\Contracts\RepositoryInterface;
use ByteTCore\Serpo\Traits\Cacheable;
use ByteTCore\Serpo\Traits\DelegatesToBuilder;
use ByteTCore\Serpo\Traits\HasCriteria;
use ByteTCore\Serpo\Traits\ResetsQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 *
 * @method static orderBy(string|array $column, string $direction = 'asc')
 * @method static groupBy(array|string ...$groups)
 */
abstract class BaseRepository implements RepositoryInterface
{
    use Cacheable;
    use DelegatesToBuilder;
    use HasCriteria;
    use ResetsQuery;

    protected Builder $query;

    public function __construct(protected Model $model)
    {
        $this->resetQuery();
    }
}
