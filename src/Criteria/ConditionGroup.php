<?php

namespace ByteTCore\Serpo\Criteria;

use ByteTCore\Serpo\Constants\Filter;

class ConditionGroup
{
    /**
     * @param  Condition[]  $children
     */
    public function __construct(
        private array $children,
        private string $boolean = Filter::AND,
    ) {}

    public function resolve(mixed $value, ?string $defaultColumn = null): NestedCriteria
    {
        return new NestedCriteria($value, [
            'children'       => $this->children,
            'group_boolean'  => $this->boolean,
        ]);
    }
}
