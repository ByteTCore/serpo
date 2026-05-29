# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Serpo** is a Laravel package (`bytetcore/serpo`) that provides repository pattern abstractions with criteria-based filtering for Eloquent models. It is distributed via Packagist and supports PHP 8.1+ and Laravel 9.x through 13.x.

- Namespace: `ByteTCore\Serpo`
- Source: `src/`
- Tests: `tests/Unit/`

## Common Commands

```bash
# Run the full test suite
composer test

# Run a specific test class
vendor/bin/phpunit --filter=WhereCriteriaTest

# Run tests with coverage
composer test:coverage

# Check code style (Laravel Pint)
composer cs

# Fix code style
composer cs:fix
```

## High-Level Architecture

### Repository Layer

`BaseRepository` (abstract) is the central abstraction. It wraps an Eloquent `Model` and holds an internal `Builder` instance. It uses Laravel's `ForwardsCalls` trait plus `__call` to proxy any method to the underlying query builder:

- If the proxied method returns a `Builder`, the repository updates its internal query and returns itself for chaining.
- If the proxied method returns anything else (e.g., `get()`, `count()`, `first()`), that result is returned directly and — **critically** — the internal query is automatically reset via `newQuery()` to prevent stale state.

Use `withoutAutoReset()` when you need to run multiple operations against the same filtered query (e.g., count then paginate).

The `filters(?array $filters)` method is the entry point for criteria-based filtering. Repositories declare a `$conditions` array that maps incoming request keys to criteria class configurations.

### Criteria System

All criteria extend `BaseCriteria` and implement `CriteriaInterface`.

Constructor signature: `__construct(mixed $value, array $config = [])`

The `apply(Builder $query): void` method mutates the query builder.

Criteria configuration in repositories supports:
- Shorthand: `'status' => WhereCriteria::class` (key becomes the column)
- Full config:
  ```php
  'keyword' => [
      'class' => LikeCriteria::class,
      'columns' => 'name|email',
      'boolean' => Filter::OR,
      'pattern' => Filter::CONTAINS,
  ]
  ```

`BaseCriteria::parseColumns()` splits pipe-separated strings (`'name|email'`) into arrays. Multi-column criteria should apply the condition to each column using the configured boolean connector.

`ByteTCore\Serpo\Constants\Filter` holds canonical string constants for operators (`GT`, `GTE`, `LT`, `LTE`, `EQUAL`, `NOT_EQUAL`), boolean connectors (`AND`, `OR`), LIKE patterns (`CONTAINS`, `STARTS_WITH`, `ENDS_WITH`), and sort directions (`ASC`, `DESC`). Prefer these over raw strings.

### Generator Commands

Three Artisan commands are registered via `SerpoServiceProvider` when running in console:

- `make:repository <Name> [--model=Model] [--service] [--force]`
- `make:service <Name> [--repository=Name] [--force]`
- `make:criteria <Name> [--force]`

Stubs live in `src/Stubs/`. The `ResolvesNamespace` trait resolves output namespaces from `config/serpo.php` (which reads env vars like `SERPO_REPOSITORY_NAMESPACE`), falling back to `App\Repositories`, `App\Services`, and `App\Criteria`.

### Test Setup

Tests extend `ByteTCore\Serpo\Tests\TestCase`, which extends Orchestra Testbench's `TestCase` and:
- Registers `SerpoServiceProvider`
- Configures an in-memory SQLite connection named `testing`

Most unit tests mock `Builder` and `Model` using Mockery. Test classes using Mockery must use the `MockeryPHPUnitIntegration` trait. The `mockBuilder()` helper pattern is common in criteria tests.

## Code Style

- Laravel Pint with PSR-12 standard
- Typed properties and return types everywhere
- Prefer arrow functions for single-expression closures
- Omit docblocks when the type signature is self-documenting

## Note on Working Tree State

There are **untracked** files in `src/Criteria/`, `src/Traits/`, and `src/Contracts/ServiceInterface.php` that currently use the `Dovutuan\Serpo` namespace. The established package namespace is `ByteTCore\Serpo`. If modifying these files, ensure the namespace is corrected to `ByteTCore\Serpo` before committing.
