<div align="center">

![Extension icon](Resources/Public/Icons/Extension.svg)

# TYPO3 extension `handlebars`

[![Coverage](https://img.shields.io/coverallsCoverage/github/CPS-IT/handlebars?logo=coveralls)](https://coveralls.io/github/CPS-IT/handlebars)
[![CGL](https://img.shields.io/github/actions/workflow/status/CPS-IT/handlebars/cgl.yaml?label=cgl&logo=github)](https://github.com/CPS-IT/handlebars/actions/workflows/cgl.yaml)
[![Tests](https://img.shields.io/github/actions/workflow/status/CPS-IT/handlebars/tests.yaml?label=tests&logo=github)](https://github.com/CPS-IT/handlebars/actions/workflows/tests.yaml)
[![Supported TYPO3 versions](https://typo3-badges.dev/badge/handlebars/typo3/shields.svg)](https://extensions.typo3.org/extension/handlebars)
[![Slack](https://img.shields.io/badge/slack-%23ext--handlebars-4a154b?logo=slack)](https://typo3.slack.com/archives/C0281DBRFCZ)

</div>

An extension for TYPO3 CMS that provides an entire rendering environment for
Handlebars templates. It is seamlessly integrated into TYPO3 and offers
extensive configuration options to get all the power out of your templates.
To meet everyone's needs, it is easily extensible using TYPO3 on-board tools.

## 🚀 Features

* **Templating engine:** Full Handlebars rendering environment for TYPO3
* **TypoScript integration:** `HANDLEBARSTEMPLATE` content object and
  additional data processors to prepare template variables
* **Component libraries:** Fractal-compatible template names (`@name`, `@name--variant`)
* **Layouts:** Layout inheritance with `extend`, `block` and `content` helpers
* **Custom helpers:** Custom helpers with auto-registration via PHP attributes
* **Extbase support:** Controller-based rendering via `HandlebarsView`
* **Caching:** Integration with TYPO3's cache framework for compiled templates
* **Extensibility:** PSR-14 events and replaceable services for all parts of
    the rendering pipeline
* **Compatibility:** Compatible with TYPO3 13.4 LTS and 14.3 LTS

## 🔥 Installation

### Composer

[![Packagist](https://img.shields.io/packagist/v/cpsit/typo3-handlebars?label=version&logo=packagist)](https://packagist.org/packages/cpsit/typo3-handlebars)
[![Packagist Downloads](https://img.shields.io/packagist/dt/cpsit/typo3-handlebars?color=brightgreen)](https://packagist.org/packages/cpsit/typo3-handlebars)

```bash
composer require cpsit/typo3-handlebars
```

### TER

[![TER version](https://typo3-badges.dev/badge/handlebars/version/shields.svg)](https://extensions.typo3.org/extension/handlebars)
[![TER downloads](https://typo3-badges.dev/badge/handlebars/downloads/shields.svg)](https://extensions.typo3.org/extension/handlebars)

Download the zip file from
[TYPO3 extension repository (TER)](https://extensions.typo3.org/extension/handlebars).

## 📙 Documentation

Please have a look at the
[official extension documentation](https://docs.typo3.org/p/cpsit/typo3-handlebars/main/en-us/).

## 🔒 Security Policy

Please read our [security policy](SECURITY.md) if you discover a security
vulnerability in this extension.

## ⭐ License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).
