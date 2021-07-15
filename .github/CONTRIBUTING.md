# Contributing

Thanks for considering contributing to this extension! :slightly_smiling_face:

Since it is an open source product, its successful further development
depends largely on improving and optimizing it together.

The development of this extension follows the official
[TYPO3 coding standards](https://github.com/TYPO3/coding-standards).
To ensure the stability and cleanliness of the code, various code
quality tools are used and most components are covered with test
cases.

## Create an issue first

Before you start working on the extension, please create an issue on
GitHub: https://github.com/CPS-IT/handlebars/issues

Also, please check if there is already an issue on the topic you want
to address.

## Contribution workflow

**Note: This extension follows [Semantic Versioning](https://semver.org/).**

### Preparation

Clone the repository first:

```bash
git clone git@github.com:CPS-IT/handlebars.git
cd handlebars
```

Now install all Composer dependencies:

```bash
composer install
```

### Check code quality

[![CGL](https://github.com/CPS-IT/handlebars/actions/workflows/cgl.yaml/badge.svg)](https://github.com/CPS-IT/handlebars/actions/workflows/cgl.yaml)

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

### Run tests

[![Tests](https://github.com/CPS-IT/handlebars/actions/workflows/tests.yaml/badge.svg)](https://github.com/CPS-IT/handlebars/actions/workflows/tests.yaml)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=CPS-IT_handlebars&metric=coverage)](https://sonarcloud.io/dashboard?id=CPS-IT_handlebars)

```bash
# Run tests
composer test

# Run tests with code coverage
composer test:ci
```

The code coverage reports will be stored in `.Build/log/coverage`.

### Build documentation

```bash
# Rebuild and open documentation
composer docs

# Build documentation (from cache)
composer docs:build

# Open rendered documentation
composer docs:open
```

The built docs will be stored in `.Build/docs`.

### Pull Request

When you have finished developing your contribution, simply submit a
pull request on GitHub: https://github.com/CPS-IT/handlebars/pulls
