<?php

namespace ByteTCore\Serpo\Criteria;

use ByteTCore\Serpo\Constants\Filter;
use Illuminate\Database\Eloquent\Builder;

class NestedCriteria extends BaseCriteria
{
    public function apply(Builder $query): void
    {
        $children = $this->config['children'] ?? [];
        $groupBoolean = $this->config['group_boolean'] ?? Filter::AND;

        $query->where(function (Builder $q) use ($children) {
            foreach ($children as $child) {
                $child->resolve($this->value)->apply($q);
            }
        }, boolean: $groupBoolean);
    }
}
