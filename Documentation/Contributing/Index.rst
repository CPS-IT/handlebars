..  include:: /Includes.rst.txt

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
cases.

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

..  _check-code-quality:

Check code quality
------------------

..  rst-class:: d-inline-block mb-3

..  image:: https://github.com/CPS-IT/handlebars/actions/workflows/cgl.yaml/badge.svg
    :target: https://github.com/CPS-IT/handlebars/actions/workflows/cgl.yaml

..  code-block:: bash

    # Run all linters
    composer lint

    # Run specific linters
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

    # Run all static code analyzers
    composer sca

    # Run specific static code analyzers
    composer sca:php

..  _run-tests:

Run tests
---------

..  image:: https://github.com/CPS-IT/handlebars/actions/workflows/tests.yaml/badge.svg
    :target: https://github.com/CPS-IT/handlebars/actions/workflows/tests.yaml

..  rst-class:: d-inline-block mb-3

..  image:: https://img.shields.io/coverallsCoverage/github/CPS-IT/handlebars?logo=coveralls
    :target: https://coveralls.io/github/CPS-IT/handlebars

..  code-block:: bash

    # Run tests
    composer test

    # Run tests with code coverage
    composer test:coverage

The code coverage reports will be stored in :file:`.Build/log/coverage`.

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

When you have finished developing your contribution, simply submit a
pull request on GitHub: https://github.com/CPS-IT/handlebars/pulls
