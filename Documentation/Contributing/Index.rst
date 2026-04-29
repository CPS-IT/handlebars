..  include:: /Includes.rst.txt

..  image:: https://img.shields.io/coverallsCoverage/github/CPS-IT/handlebars?logo=coveralls
    :target: https://coveralls.io/github/CPS-IT/handlebars

..  image:: https://img.shields.io/github/actions/workflow/status/CPS-IT/handlebars/ci.yaml?label=CI&logo=github
    :target: https://github.com/CPS-IT/handlebars/actions/workflows/ci.yaml

..  _contributing:

============
Contributing
============

Thanks for considering contributing to this extension! Since it is
an open source product, its successful further development depends
largely on improving and optimizing it together.

The development of this extension follows the official
`TYPO3 coding standards <https://github.com/TYPO3/coding-standards>`__.
To ensure the stability and cleanliness of the code, various code
quality tools are used and most components are covered with test
cases. In addition, we use `DDEV <https://ddev.readthedocs.io/en/stable/>`__
for local development. Make sure to set it up as described below. For
continuous integration, we use GitHub Actions.

..  _create-an-issue-first:

Create an issue first
=====================

Before you start working on the extension, please create an issue on
GitHub: https://github.com/CPS-IT/handlebars/issues

Also, please check if there is already an issue on the topic you want
to address.

..  _contribution-workflow:

Contribution workflow
=====================

..  note::

    This extension follows `Semantic Versioning <https://semver.org/>`__.

..  _preparation:

Preparation
-----------

Clone the repository first:

..  code-block:: bash

    git clone https://github.com/CPS-IT/handlebars.git
    cd handlebars

Now install all Composer dependencies:

..  code-block:: bash

    composer install

..  _analyze-code:

Analyze code
------------

..  _check-code-quality:

..  code-block:: bash

    # All analyzers
    composer analyze

    # Specific analyzers
    composer analyze:dependencies

Check code quality
------------------

..  code-block:: bash

    # All linters
    composer lint

    # Specific linters
    composer lint:composer
    composer lint:editorconfig
    composer lint:php
    composer lint:typoscript

    # Fix all CGL issues
    composer fix

    # Fix specific CGL issues
    composer fix:composer
    composer fix:editorconfig
    composer fix:php
    composer fix:typoscript

    # All static code analyzers
    composer sca

    # Specific static code analyzers
    composer sca:php

..  _run-tests:

Run tests
---------

..  code-block:: bash

    # All tests
    composer test

    # Specific tests
    composer test:functional
    composer test:unit

    # All tests with code coverage
    composer test:coverage

    # Specific tests with code coverage
    composer test:coverage:functional
    composer test:coverage:unit

    # Merge code coverage of all test suites
    composer test:coverage:merge

Code coverage reports are written to :file:`.Build/coverage`. You can
open the last merged HTML report like follows:

..  code-block:: bash

    open .Build/coverage/html/_merged/index.html

..  _build-documentation:

Build documentation
-------------------

..  code-block:: bash

    # Rebuild and open documentation
    composer docs

    # Build documentation (from cache)
    composer docs:build

    # Open rendered documentation
    composer docs:open

The built docs will be stored in :file:`.Build/docs`.

..  _pull-request:

Pull Request
------------

Once you have finished your work, please **submit a pull request** and describe
what you've done: https://github.com/CPS-IT/handlebars/pulls

Ideally, your PR references an issue describing the problem
you're trying to solve. All described code quality tools are automatically
executed on each pull request for all currently supported PHP versions and TYPO3
versions.
