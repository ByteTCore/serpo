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
- ⚡ **Zero Boilerplate** — Artisan generators for repositories, services, and criteria
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

Define reusable filter conditions in your repository:

```php
use ByteTCore\Serpo\Criteria\WhereCriteria;
use ByteTCore\Serpo\Criteria\LikeCriteria;
use ByteTCore\Serpo\Criteria\DateCriteria;
use ByteTCore\Serpo\Constants\Filter;

class UserRepository extends BaseRepository
{
    protected array $conditions = [
        // Simple equality (default operator is '=')
        'status' => WhereCriteria::class,

        // Search across multiple columns
        'keyword' => [
            'class' => LikeCriteria::class,
            'columns' => 'name|email',
            'boolean' => Filter::OR,
            'pattern' => Filter::CONTAINS,
        ],

        // Comparison operators via params
        'min_age' => [
            'class' => WhereCriteria::class,
            'columns' => 'age',
            'operator' => Filter::GTE,
        ],

        // Date filtering
        'created_after' => [
            'class' => DateCriteria::class,
            'columns' => 'created_at',
            'operator' => Filter::GTE,
        ],
    ];

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

### Available Criteria

| Criteria | Description | Params |
|---|---|---|
| `WhereCriteria` | `WHERE col op val` | `operator`: `=`, `<>`, `>`, `>=`, `<`, `<=` |
| `LikeCriteria` | `WHERE col LIKE pattern` | `boolean`: `or`, `and`; `pattern`: `contains`, `starts_with`, `ends_with` |
| `DateCriteria` | `WHERE DATE(col) op val` | `operator`: `=`, `<>`, `>`, `>=`, `<`, `<=` |
| `BetweenCriteria` | `WHERE col BETWEEN val[0] AND val[1]` | — |
| `NotBetweenCriteria`| `WHERE col NOT BETWEEN val[0] AND val[1]` | — |
| `YearCriteria` | `WHERE YEAR(col) op val` | `operator`: `=`, `<>`, `>`, `>=`, `<`, `<=` |
| `MonthCriteria` | `WHERE MONTH(col) op val` | `operator`: `=`, `<>`, `>`, `>=`, `<`, `<=` |
| `InCriteria` | `WHERE col IN (...)` | — |
| `NotInCriteria` | `WHERE col NOT IN (...)` | — |
| `NullCriteria` | `WHERE col IS NULL` | — |
| `NotNullCriteria` | `WHERE col IS NOT NULL` | — |
| `JsonContainsCriteria` | `whereJsonContains` | — |
| `JsonNotContainsCriteria` | `whereJsonDoesntContain` | — |
| `OrderByCriteria` | `ORDER BY col (asc/desc)` | — |

### Detailed Criteria Walkthrough

#### Database Comparisons
```php
'status' => WhereCriteria::class, // Checks if 'status' matches input
'price_over' => [
    'class' => WhereCriteria::class,
    'columns' => 'price',
    'operator' => Filter::GTE // ->where('price', '>=', input)
]
```

#### Search & Text (LikeCriteria)
Automatically supports SQL padding (%) so you don't have to inject % in strings.
```php
'keyword' => [
    'class' => LikeCriteria::class,
    'columns' => 'title|content|author',
    'boolean' => Filter::OR, // ->where('title', 'like', '%val%') OR ->where('content', ...)
    'pattern' => Filter::CONTAINS, // Optional. Other options: STARTS_WITH, ENDS_WITH
]
```

#### Ranges (BetweenCriteria)
Expects an array of precisely two values: `[$start, $end]`.
```php
'price_range' => [
    'class' => BetweenCriteria::class,
    'columns' => 'price' // expects $request->price_range = [100, 500]
]
```

#### Working with Dates
Filter via specific date, year, or month. Extremely useful for reporting dashboards.
```php
'created_at' => DateCriteria::class, // Date match YYYY-MM-DD
'birth_year' => YearCriteria::class,
'birth_month' => MonthCriteria::class,
```

#### Sets & Arrays
Checks whether a column is among an array of inputs.
```php
'tags' => InCriteria::class,    // Expects $request->tags = ['php', 'laravel']
'ignore' => NotInCriteria::class, // Exclude IDs
```

#### Nullity Checks
Pass a truthy value (`true`, `1`) to trigger these.
```php
'unverified' => NullCriteria::class,    // ->whereNull('email_verified_at')
'active_only' => NotNullCriteria::class // ->whereNotNull('email_verified_at')
```

#### JSON Columns
Filter based on JSON arrays directly in the database.
```php
'has_tag' => JsonContainsCriteria::class, // ->whereJsonContains('tags', input)
```

#### Dynamic Sorting (OrderByCriteria)
Pass `Filter::ASC` or `Filter::DESC` to dynamically sort records dynamically instead of hardcoding `->orderBy()`.
```php
'sort_date' => [
    'class' => OrderByCriteria::class,
    'columns' => 'created_at' // expects $request->sort_date = Filter::DESC
]
```

### Custom Criteria

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
];
```

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
