# Development

The development of the extension follows the official
[TYPO3 Coding Standards](https://github.com/TYPO3/coding-standards). To ensure stability, relevant
code components are also covered by Unit tests.

## :octicons-terminal-24: Preparation

```bash
# Clone repository
git clone git@github.com:CPS-IT/handlebars.git
cd handlebars

# Install Composer dependencies
composer install

```

## :octicons-file-code-24: Check code quality

```bash
# Run all linters
composer lint

# Run PHP linter only
composer lint:php

# Run TypoScript linter only
composer lint:typoscript

# Run PHP static code analysis
composer sca

# Run Composer normalization
composer normalize
```

## :octicons-bug-24: Run tests

```bash
# Run tests
composer test

# Run tests with code coverage
composer test:ci
```

The code coverage reports will be stored in `.Build/log/coverage`.

## :material-file-document-edit-outline: Build documentation

```bash
# Build and watch docs (starts Docker container)
composer docs

# Stop Docker container
composer docs:stop

# Open currently building documentation
composer docs:open

# Build docs only
composer docs:build
```

The built docs will be stored in `.Build/docs`.
