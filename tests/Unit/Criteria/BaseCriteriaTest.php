<?php

namespace ByteTCore\Serpo\Tests\Unit\Criteria;

use ByteTCore\Serpo\Criteria\BaseCriteria;
use ByteTCore\Serpo\Exceptions\InvalidCriteriaException;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\TestCase;

class BaseCriteriaTest extends TestCase
{
    private function makeCriteria(string|array $columns, mixed $value = 'test', string $boolean = 'and'): BaseCriteria
    {
        return new class($value, ['columns' => $columns, 'boolean' => $boolean]) extends BaseCriteria {
            public function apply(Builder $query): void {}

            public function getExposedColumns(): string|array
            {
                // we call it exposed so we don't conflict with base class protected method
                return parent::getColumns();
            }

            public function getExposedBoolean(): string
            {
                return parent::getBoolean();
            }

            public function getValue(): mixed
            {
                return $this->value;
            }

            public function exposeParsedColumns(): array
            {
                return $this->parseColumns();
            }
        };
    }

    public function test_accepts_string_column(): void
    {
        $criteria = $this->makeCriteria('name');

        $this->assertSame('name', $criteria->getExposedColumns());
    }

    public function test_accepts_array_columns(): void
    {
        $criteria = $this->makeCriteria(['name', 'email']);

        $this->assertSame(['name', 'email'], $criteria->getExposedColumns());
    }

    public function test_accepts_pipe_separated_columns(): void
    {
        $criteria = $this->makeCriteria('name|email');

        $this->assertSame(['name', 'email'], $criteria->exposeParsedColumns());
    }

    public function test_parse_columns_returns_array_as_is(): void
    {
        $criteria = $this->makeCriteria(['name', 'email']);

        $this->assertSame(['name', 'email'], $criteria->exposeParsedColumns());
    }

    public function test_stores_value_and_boolean(): void
    {
        $criteria = $this->makeCriteria('name', 'John', 'or');

        $this->assertSame('John', $criteria->getValue());
        $this->assertSame('or', $criteria->getExposedBoolean());
    }

    public function test_default_boolean_is_and(): void
    {
        $criteria = $this->makeCriteria('name');

        $this->assertSame('and', $criteria->getExposedBoolean());
    }

    public function test_throws_on_empty_string_column(): void
    {
        $this->expectException(InvalidCriteriaException::class);

        $this->makeCriteria('');
    }

    public function test_throws_on_whitespace_string_column(): void
    {
        $this->expectException(InvalidCriteriaException::class);

        $this->makeCriteria('  ');
    }

    public function test_throws_on_string_with_spaces(): void
    {
        $this->expectException(InvalidCriteriaException::class);

        $this->makeCriteria('first name');
    }

    public function test_throws_on_empty_array(): void
    {
        $this->expectException(InvalidCriteriaException::class);

        $this->makeCriteria([]);
    }

    public function test_throws_on_array_with_empty_string(): void
    {
        $this->expectException(InvalidCriteriaException::class);

        $this->makeCriteria(['name', '']);
    }

    public function test_throws_on_array_with_non_string(): void
    {
        $this->expectException(InvalidCriteriaException::class);

        $this->makeCriteria(['name', 123]);
    }
}
