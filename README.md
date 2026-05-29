# Serpo — Laravel Repository Pattern

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bytetcore/serpo.svg?style=flat-square)](https://packagist.org/packages/bytetcore/serpo)
[![License](https://img.shields.io/packagist/l/bytetcore/serpo.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/bytetcore/serpo.svg?style=flat-square)](composer.json)
[![Latest Stable Version](http://poser.pugx.org/ByteTCore/serpo/v)](https://packagist.org/packages/ByteTCore/serpo)
[![Total Downloads](http://poser.pugx.org/ByteTCore/serpo/downloads)](https://packagist.org/packages/ByteTCore/serpo)
[![Latest Unstable Version](http://poser.pugx.org/ByteTCore/serpo/v/unstable)](https://packagist.org/packages/ByteTCore/serpo)

Elegant data layer abstraction for Laravel using the **Repository Pattern** with powerful **criteria-based filtering**. Keep your controllers clean and your data logic reusable.

## Features

- 🏗️ **Repository Pattern** — Abstract Eloquent models behind clean interfaces
- 🔍 **Criteria System** — Composable, reusable query filters (where, like, date, JSON, null, in)
- ⚡ **Query Caching** — Redis-backed query cache with auto-key generation and configurable TTL
- 🔧 **Zero Boilerplate** — Artisan generators for repositories, services, and criteria
- 🔗 **Fluent Chaining** — Chain any Eloquent Builder method directly on repositories
- 🔄 **Auto Query Reset** — Query state resets after execution, preventing stale queries
- 📦 **Laravel Auto-Discovery** — Install and go, no manual provider registration

## Requirements

- PHP 8.1+
- Laravel 9.0+

## Installation

```bash
composer require bytetcore/serpo
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=serpo-config
```

## Quick Start

### 1. Generate a Repository

```bash
# Basic repository
php artisan make:repository UserRepository

# With a specific model
php artisan make:repository UserRepository --model=User

# With a corresponding service class
php artisan make:repository UserRepository --model=User --service
```

### 2. Generate a Service

```bash
php artisan make:service UserService
```

### 3. Generate a Custom Criteria

```bash
php artisan make:criteria ActiveUserCriteria
```

## Usage

### Basic Repository

```php
namespace App\Repositories;

use App\Models\User;
use ByteTCore\Serpo\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}
```

### Query Methods

```php
$repo = app(UserRepository::class);

// Get all records
$users = $repo->all();

// Get with specific columns
$users = $repo->get(['id', 'name', 'email']);

// Get first record
$user = $repo->first();

// Get first or throw exception
$user = $repo->firstOrFail();

// Get latest record
$user = $repo->last();
$user = $repo->last('updated_at');
```

### Eloquent Builder Chaining

All Eloquent Builder methods are available directly on the repository:

```php
$users = $repo->where('active', true)
    ->orderBy('name')
    ->limit(10)
    ->get();

$count = $repo->where('role', 'admin')->count();

$users = $repo->with('posts')->whereHas('posts')->get();
```

### Criteria-Based Filtering

Define reusable filters in your repository using the fluent `Condition` API:

```php
use ByteTCore\Serpo\Criteria\Condition;
use App\Criteria\ActiveUserCriteria;

class UserRepository extends BaseRepository
{
    protected function conditions(): array
    {
        return [
            'status'   => Condition::where(),
            'keyword'  => Condition::like('name', 'email')->or(),
            'min_age'  => Condition::where('age')->gte(),
            'created_from' => Condition::date('created_at')->gte(),
            'price_between' => Condition::between('price'),
            'tags'     => Condition::whereIn('tags'),
            'deleted'  => Condition::whereNull('deleted_at'),
            'birth_year' => Condition::year('birth_date'),
            'active'   => Condition::custom(ActiveUserCriteria::class),
            'search'   => Condition::orGroup(
                Condition::like('name'),
                Condition::where('email'),
            ),
        ];
    }

    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}
```

Apply filters from request data:

```php
// In your controller
$users = $repo->filters($request->only(['status', 'keyword', 'min_age']))->get();
```

### Controlling Filter Application Order

By default, filters are applied in the order declared in `conditions()`. Override `filterOrder()` to change the order without reordering the `conditions()` map:

```php
protected function filterOrder(): array
{
    return [
        'keyword',  // applied first
        'status',   // applied second
    ];
    // 'min_age', 'created_from', ... — any keys not listed are appended in original order
}
```

### Available Criteria

| Factory | Criteria | SQL |
|---|---|---|
| `Condition::where()` | WhereCriteria | `WHERE col op val` |
| `Condition::like()` | LikeCriteria | `WHERE col LIKE pattern` |
| `Condition::date()` | DateCriteria | `WHERE DATE(col) op val` |
| `Condition::between()` | BetweenCriteria | `WHERE col BETWEEN ? AND ?` |
| `Condition::notBetween()` | NotBetweenCriteria | `WHERE col NOT BETWEEN ? AND ?` |
| `Condition::year()` | YearCriteria | `WHERE YEAR(col) op val` |
| `Condition::month()` | MonthCriteria | `WHERE MONTH(col) op val` |
| `Condition::whereIn()` | InCriteria | `WHERE col IN (...)` |
| `Condition::whereNotIn()` | NotInCriteria | `WHERE col NOT IN (...)` |
| `Condition::whereNull()` | NullCriteria | `WHERE col IS NULL` |
| `Condition::whereNotNull()` | NotNullCriteria | `WHERE col IS NOT NULL` |
| `Condition::jsonContains()` | JsonContainsCriteria | `whereJsonContains` |
| `Condition::jsonNotContains()` | JsonNotContainsCriteria | `whereJsonDoesntContain` |
| `Condition::orderBy()` | OrderByCriteria | `ORDER BY col asc/desc` |
| `Condition::andGroup(...)` | NestedCriteria | Nested `AND` group |
| `Condition::orGroup(...)` | NestedCriteria | Nested `OR` group |
| `Condition::custom()` | Any custom class | — |

### Fluent modifiers

| Modifier | Applies to | Example |
|---|---|---|
| `.columns(...)` | All | `Condition::where('name', 'email')` |
| `.and()` / `.or()` | All | `Condition::like('name', 'email')->or()` |
| `.gt()` / `.gte()` / `.lt()` / `.lte()` | where, date, year, month | `Condition::where('age')->gte()` |
| `.not()` | where | `Condition::where('status')->not()` |
| `.contains()` / `.startsWith()` / `.endsWith()` | like | `Condition::like('name')->startsWith()` |
| `.asc()` / `.desc()` | orderBy | `Condition::orderBy('created_at')->desc()` |

### Detailed Criteria Walkthrough

#### Database Comparisons
```php
'status'     => Condition::where(),              // WHERE status = input
'price_over' => Condition::where('price')->gte(), // WHERE price >= input
'not_admin'  => Condition::where('role')->not(), // WHERE role != input
```

#### Search & Text (LikeCriteria)
```php
'keyword' => Condition::like('title', 'content', 'author')->or(),
// WHERE (title LIKE '%val%' OR content LIKE '%val%' OR author LIKE '%val%')

'prefix' => Condition::like('name')->startsWith(),
// WHERE name LIKE 'val%'

'suffix' => Condition::like('name')->endsWith(),
// WHERE name LIKE '%val'
```

#### Ranges (BetweenCriteria)
```php
'price_range' => Condition::between('price'),
// expects $request->price_range = [100, 500]
```

#### Working with Dates
```php
'created_at'  => Condition::date('created_at'),
'birth_year'  => Condition::year('birth_date'),
'birth_month' => Condition::month('birth_date'),
```

#### Sets & Arrays
```php
'tags'   => Condition::whereIn('tags'),
'ignore' => Condition::whereNotIn('id'),
```

#### Nullity Checks
```php
'unverified' => Condition::whereNull('email_verified_at'),
'active'     => Condition::whereNotNull('email_verified_at'),
```

#### JSON Columns
```php
'has_tag' => Condition::jsonContains('tags'),
```

#### Dynamic Sorting
```php
'sort_by' => Condition::orderBy('created_at')->desc(),
```

#### Nested Groups
Combine different criteria types in a single parenthesized group:
```php
'search' => Condition::orGroup(
    Condition::like('name'),
    Condition::where('email'),
),
// WHERE ((name LIKE '%val%') OR (email = 'val'))

'date_range' => Condition::andGroup(
    Condition::date('created_at')->gte(),
    Condition::date('created_at')->lte(),
),
// WHERE ((DATE(created_at) >= 'val') AND (DATE(created_at) <= 'val'))
```

### Custom Criteria

Create your own criteria by extending `BaseCriteria`, then reference it with `Condition::custom()`:

```php
namespace App\Criteria;

use ByteTCore\Serpo\Criteria\BaseCriteria;
use Illuminate\Database\Eloquent\Builder;

class ActiveWithRecentPostsCriteria extends BaseCriteria
{
    public function apply(Builder $query): void
    {
        $query->where('active', true)
            ->whereHas('posts', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays(30)));
    }
}
```

```php
// In your repository:
use App\Criteria\ActiveWithRecentPostsCriteria;

protected function conditions(): array
{
    return [
        'recent_active' => Condition::custom(ActiveWithRecentPostsCriteria::class),
    ];
}
```

### Auto-Reset Behavior

By default, the query builder resets after each execution to prevent stale state:

```php
$active = $repo->where('active', true)->get();   // query resets after get()
$all = $repo->all();                              // fresh query — returns all records
```

Disable auto-reset when you need to reuse the query:

```php
$repo->withoutAutoReset()
    ->where('active', true);

$count = $repo->count();       // same filtered query
$users = $repo->get();         // same filtered query
```

### Caching

Cache query results to reduce database load. Enable it in `config/serpo.php` by setting `cache.enabled` to `true`.

Call `cache()` before any terminal method (`get`, `paginate`, `first`, `count`, etc.) to cache the result:

```php
$repo = app(UserRepository::class);

// Cache a get() result with auto-generated key (SHA-256 of table + filters)
$users = $repo->filters(['status' => 'active'])->cache()->get();

// Custom key + custom TTL
$users = $repo->filters(['status' => 'active'])->cache(key: 'active_users', ttl: 300)->get();

// Works with any terminal method
$paginated = $repo->filters($filters)->cache(key: 'search_results')->paginate(10);
$first = $repo->cache(key: 'latest')->latest()->first();
$count = $repo->filters(['role' => 'admin'])->cache(key: 'admin_count')->count();

// Delete all entries for a specific custom key
$repo->forget('active_users');

// Flush all cached queries for this repository's model
$repo->flush();
```

**How it works:**

- `cache()` marks the query — does not execute yet
- The next terminal method (`get`, `paginate`, `first`, `count`, ...) checks cache before executing and stores the result after
- Cache key format: `{custom_or_hash}:{method}:{params_hash}` — different method+params produce different cache entries under the same custom key
- `forget('key')` scans and deletes all entries starting with that key prefix
- `flush()` clears all cached entries for this model's table

## Configuration

```php
// config/serpo.php
return [
    'repository' => [
        'namespace' => env('SERPO_REPOSITORY_NAMESPACE', 'Repositories'),
    ],
    'service' => [
        'namespace' => env('SERPO_SERVICE_NAMESPACE', 'Services'),
    ],
    'criteria' => [
        'namespace' => env('SERPO_CRITERIA_NAMESPACE', 'Criteria'),
    ],
    'cache' => [
        'enabled' => env('SERPO_CACHE_ENABLED', false),
        'driver'  => env('SERPO_CACHE_DRIVER', 'redis'),
        'ttl'     => env('SERPO_CACHE_TTL', 3600),
        'prefix'  => env('SERPO_CACHE_PREFIX', 'serpo'),
    ],
];
```

| Config Key | Env Variable | Default | Description |
|---|---|---|---|
| `repository.namespace` | `SERPO_REPOSITORY_NAMESPACE` | `Repositories` | Default namespace for generated repositories |
| `service.namespace` | `SERPO_SERVICE_NAMESPACE` | `Services` | Default namespace for generated services |
| `criteria.namespace` | `SERPO_CRITERIA_NAMESPACE` | `Criteria` | Default namespace for generated criteria |
| `cache.enabled` | `SERPO_CACHE_ENABLED` | `false` | Toggle repository caching on/off |
| `cache.driver` | `SERPO_CACHE_DRIVER` | `redis` | Cache store driver (only Redis supported) |
| `cache.ttl` | `SERPO_CACHE_TTL` | `3600` | Default cache lifetime in seconds |
| `cache.prefix` | `SERPO_CACHE_PREFIX` | `serpo` | Cache key prefix to avoid collisions |

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

Licensed under the [Apache License 2.0](LICENSE).
