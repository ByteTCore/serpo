# Contributing to Serpo

Thank you for considering contributing to Serpo! This document provides guidelines to help your contribution process go smoothly.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and harassment-free environment for everyone.

## How to Contribute

### Reporting Bugs

Before creating a bug report, please check existing issues to avoid duplicates. When filing a bug, include:

- **PHP and Laravel versions**
- **Steps to reproduce** the issue
- **Expected behavior** vs **actual behavior**
- **Code samples** that demonstrate the problem

### Suggesting Features

Open an issue with the `feature-request` label. Describe:

- The problem your feature solves
- Your proposed solution
- Alternative approaches you've considered

### Pull Requests

1. **Fork** the repository
2. **Create a branch** from `master`: `git checkout -b feature/your-feature`
3. **Write tests** for your changes
4. **Follow the code style** (run `composer cs:fix`)
5. **Run the test suite**: `composer test`
6. **Commit** using [Conventional Commits](https://www.conventionalcommits.org/):
   - `feat: add new criteria type`
   - `fix: correct InCriteria query method`
   - `docs: update README examples`
   - `refactor: simplify BaseCriteria validation`
7. **Push** to your fork and submit a **Pull Request**

## Development Setup

```bash
# Clone your fork
git clone https://github.com/your-username/serpo.git
cd serpo

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer cs

# Fix code style
composer cs:fix
```

## Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) for code formatting. Run before committing:

```bash
composer cs:fix
```

Key conventions:

- **PSR-12** coding standard
- Use typed properties and return types
- Prefer arrow functions for single-expression closures
- No unnecessary docblocks (if the type signature is clear)

## Testing

All changes must include tests. We use **PHPUnit** with **Orchestra Testbench**.

```bash
# Run full suite
composer test

# Run specific test
vendor/bin/phpunit --filter=WhereCriteriaTest
```

### Test Structure

```
tests/
├── TestCase.php          # Base test class with Orchestra Testbench
└── Unit/
    ├── Criteria/         # One test file per criteria class
    └── Repository/       # Repository behavior tests
```

## Versioning

This project follows [Semantic Versioning](https://semver.org/):

- **MAJOR** — Breaking changes to public API
- **MINOR** — New features, backward compatible
- **PATCH** — Bug fixes, backward compatible

## License

By contributing, you agree that your contributions will be licensed under the [Apache License 2.0](LICENSE).
