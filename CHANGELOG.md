## [1.0.1] - 2026-04-06
- chore: update PHP and Laravel versions in tests configuration (f89e561)
- chore: update PHP and Laravel versions in tests configuration (5fcdfa7)
- chore: update PHP and Laravel versions in tests configuration (05b8a97)
- chore: update PHP and Laravel versions in tests configuration (53ec48c)
- chore: update PHP and Laravel versions in tests configuration (72749f7)
- chore: update PHP and Laravel versions in tests configuration (9cd474a)
- chore: update PHP and Laravel versions in tests configuration (bd5f779)
- Merge remote-tracking branch 'origin/master' (0b4dc85)
- update PHPUnit version constraints and improve code formatting (0256ce8)

## [1.0.0] - 2026-04-06
Initial release

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-04-06

### Added

- `WhereCriteria` — unified comparison criteria with configurable `operator` param (`=`, `<>`, `>`, `>=`, `<`, `<=`)
- `LikeCriteria` — unified like criteria with `operator` (`like`, `not like`) and `pattern` (`contains`, `starts_with`, `ends_with`) params
- `DateCriteria` — unified date criteria with configurable `operator` param
- `JsonContainsCriteria` and `JsonNotContainsCriteria` with clearer naming
- `ResolvesNamespace` shared trait for artisan commands
- `RepositoryInterface` now defines all query method signatures
- `params` option in conditions config for passing extra constructor arguments to criteria
- Null-safe `filters()` — passing `null` returns early instead of erroring
- Full test suite with PHPUnit and Orchestra Testbench
- `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`, `.gitattributes`, `.editorconfig`

### Changed

- **BREAKING:** Namespace changed from `Dovutuan\Serpo` to `ByteTCore\Serpo`
- **BREAKING:** Minimum PHP version raised from 8.0 to 8.1
- **BREAKING:** Dropped Laravel 7.x and 8.x support (minimum Laravel 9.x)
- **BREAKING:** `ServiceProvider` renamed to `SerpoServiceProvider`
- **BREAKING:** `InvalidCriteriaException::forFields()` replaced with `invalidFormat()`, `emptyColumns()`, `invalidArrayItem()`
- **BREAKING:** `JsonCriteria` renamed to `JsonContainsCriteria`
- **BREAKING:** `JsonNotContainCriteria` renamed to `JsonNotContainsCriteria`
- License changed from MIT to Apache 2.0
- `BaseCriteria` now accepts `mixed` value type instead of `string|int`
- `BaseRepository` merged all traits inline (no more `HasCriteria`, `QueryTrait`, `QueryBuilderTrait`)
- `initInstance()` renamed to `resetQuery()` for clarity
- Config env variables prefixed with `SERPO_` to avoid collisions
- Publish tag changed from `serpo` to `serpo-config`

### Removed

- **BREAKING:** Removed 13 single-operator criteria classes (`EqualCriteria`, `NotEqualCriteria`, `GreaterCriteria`, `GreaterEqualCriteria`, `LessCriteria`, `LessEqualCriteria`, `AfterLikeCriteria`, `BeforeLikeCriteria`, `NotLikeCriteria`, `DateNotEqualCriteria`, `DateGreaterCriteria`, `DateGreaterEqualCriteria`, `DateLessCriteria`, `DateLessEqualCriteria`) — use `WhereCriteria`, `LikeCriteria`, or `DateCriteria` with operator params instead
- Removed empty `Cacheable` and `Filterable` traits
- Removed empty `ServiceInterface`
- Removed separate `ValidateCriteria` and `ParseColumnCriteria` traits (merged into `BaseCriteria`)
- Removed separate `HasCriteria`, `QueryTrait`, `QueryBuilderTrait` traits (merged into `BaseRepository`)
- Removed `squizlabs/php_codesniffer` dev dependency (replaced by `laravel/pint`)

### Fixed

- **BUG:** `InCriteria` was incorrectly calling `whereNotIn` — now correctly calls `whereIn`
- **BUG:** `NotInCriteria` was incorrectly calling `whereIn` — now correctly calls `whereNotIn`
- `HasCriteria::filters()` crashed on `null` input — now returns `$this` gracefully