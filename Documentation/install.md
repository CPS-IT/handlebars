# Installation

## Requirements

* PHP 7.1+
* Composer v1 or v2
* TYPO3 CMS 10.4 LTS

## Composer

```bash
composer require cpsit/typo3-handlebars
```

## Define dependencies

!!! important
    This is an essential step to ensure service configuration is interpreted correctly.

Each extension depending on `EXT:handlebars` needs to explicitly define it as dependency
in its `ext_emconf.php` file:

```php linenums="1"
# ext_emconf.php

$EM_CONF[$_EXTKEY] = [
    'constraints' => [
        'depends' => [
            'handlebars' => '0.2.0-0.99.99',
        ],
    ],
];
```

Otherwise, template paths are not evaluated in the right order and might be overridden.
