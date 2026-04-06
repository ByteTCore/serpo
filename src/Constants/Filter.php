<?php

namespace ByteTCore\Serpo\Constants;

/**
 * Common constants for repository filtering.
 * Centralizing strings prevents typos in configurations.
 */
class Filter
{
    /** @var string Boolean AND connector */
    public const AND = 'and';

    /** @var string Boolean OR connector */
    public const OR = 'or';

    /** @var string Equality operator */
    public const EQUAL = '=';

    /** @var string Inequality operator */
    public const NOT_EQUAL = '!=';

    /** @var string Greater than operator */
    public const GT = '>';

    /** @var string Greater than or equal operator */
    public const GTE = '>=';

    /** @var string Less than operator */
    public const LT = '<';

    /** @var string Less than or equal operator */
    public const LTE = '<=';

    /** @var string String comparison LIKE operator */
    public const LIKE = 'like';

    /** @var string String comparison NOT LIKE operator */
    public const NOT_LIKE = 'not like';

    /** @var string String comparison ILIKE operator (PostgreSQL) */
    public const ILIKE = 'ilike';

    /** @var string String comparison NOT ILIKE operator (PostgreSQL) */
    public const NOT_ILIKE = 'not ilike';

    /** @var string IN operator for arrays */
    public const IN = 'in';

    /** @var string NOT IN operator for arrays */
    public const NOT_IN = 'not in';

    /** @var string NULL check key */
    public const IS_NULL = 'null';

    /** @var string NOT NULL check key */
    public const IS_NOT_NULL = 'not null';

    /** @var string Pattern for matching anywhere in string */
    public const CONTAINS = 'contains';

    /** @var string Pattern for matching start of string */
    public const STARTS_WITH = 'starts_with';

    /** @var string Pattern for matching end of string */
    public const ENDS_WITH = 'ends_with';

    /** @var string Ascending sort direction */
    public const ASC = 'asc';

    /** @var string Descending sort direction */
    public const DESC = 'desc';
}
