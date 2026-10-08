# Security Policy

This document describes how to report security vulnerabilities affecting this repository
and the released versions of this product.

For our company-wide vulnerability disclosure policy, scope, safe-harbor terms, and further
contact options, please see our [Security Policy](https://www.cps-it.de/security-policy).

## Supported Versions

Security fixes are normally provided for the following supported versions:

| Version | Supported          |
|---------| ------------------ |
| 1.x     | :white_check_mark: |
| < 1.x   | :x:                |

Reports affecting unsupported versions are still welcome. Depending on the severity,
exploitability, and affected users, we may provide a mitigation, guidance, or an exceptional
fix; however, this cannot be guaranteed.

## Reporting a Vulnerability

> [!CAUTION]
> Please do **not** report security vulnerabilities through public GitHub issues, discussions,
> pull requests, or any other public channel.

Please report suspected vulnerabilities privately via our preferred reporting channel:

- [CPS vulnerability disclosure form](https://www.cps-it.de/security)

If private vulnerability reporting is enabled for this repository, you may alternatively use
GitHub's [Report a vulnerability](https://github.com/CPS-IT/handlebars/security/advisories/new)
feature.

Please report third-party dependency vulnerabilities primarily to the relevant upstream maintainer.
If you believe they affect this product, we would still appreciate being informed through the
reporting channel above.

### What to include

Please include as much of the following information as possible:

- A description of the vulnerability and its potential impact
- The affected product, package, component, and version(s)
- The version you tested, if different from the affected version range
- Prerequisites for exploitation, such as authentication level or configuration
- Steps to reproduce the issue or a non-destructive proof of concept
- Known indicators of active exploitation, if applicable
- Any possible mitigation or workaround you have identified
- Your preferred contact details and acknowledgement name, if you would like credit

Please do not include passwords, API tokens, private keys, session cookies, complete database dumps,
or unnecessary personal data. If sensitive information needs to be shared, use the reporting form
first so that we can coordinate an appropriate secure exchange channel.

### What to expect

- We aim to acknowledge reports within **2 business days**, often sooner.
- We will assess the report, contact you if we need further information, and coordinate remediation
  where appropriate.
- We will provide status updates at reasonable intervals while a confirmed issue is being investigated
  or remediated.
- We will acknowledge your contribution in a security advisory if you request it and if doing so is
  appropriate.

## Coordinated Disclosure

We follow a coordinated vulnerability disclosure process. We aim to agree on a public disclosure
timeline with the reporter after validating the issue and assessing remediation options.

A 90-day period from the initial report is our general target for coordinated disclosure where
appropriate. The timeline may be shorter for actively exploited vulnerabilities or serious security
incidents, and may be adjusted where a safe and effective remediation requires more time.

Please do not publicly disclose the vulnerability before we have had a reasonable opportunity to
investigate and address it, or before we have agreed on a disclosure date together.

## Scope

This policy applies to this repository and its officially released versions, unless a more specific
security policy is published for the product.

For detailed scope information, including CPS products, official distribution channels, third-party
dependencies, end-of-life versions, and safe-harbor conditions, see the central
[Security Policy](https://www.cps-it.de/security-policy).
