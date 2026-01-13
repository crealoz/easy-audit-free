# EasyAudit Module

[![Latest Stable Version](https://img.shields.io/packagist/v/crealoz/easy-audit-free.svg?style=flat-square)](https://packagist.org/packages/crealoz/easy-audit-free)
[![License: MIT](https://img.shields.io/github/license/crealoz/easy-audit-free.svg?style=flat-square)](./LICENSE)
[![Packagist](https://img.shields.io/packagist/dt/crealoz/easy-audit-free.svg?style=flat-square)](https://packagist.org/packages/crealoz/easy-audit-free/stats)
[![Packagist](https://img.shields.io/packagist/dm/crealoz/easy-audit-free.svg?style=flat-square)](https://packagist.org/packages/crealoz/easy-audit-free/stats)
[![codecov](https://codecov.io/gh/crealoz/easy-audit-free/graph/badge.svg?token=CKH0L0G395)](https://codecov.io/gh/crealoz/easy-audit-free)

EasyAudit is a Magento 2 module that audits your codebase and configuration to highlight potential issues and provide actionable insights. It helps developers and site owners improve Magento applications' quality, performance, and maintainability.


## Stack and Project Overview

- Language: PHP (>= 8.1)
- Framework / Platform: Magento 2 (module type: magento2-module)
- Package manager: Composer
- Tests: PHPUnit
- Entry points:
  - Magento CLI command: `crealoz:audit:run`
  - Magento Admin UI (grids and configuration)

For more details on the processors used in EasyAudit, see docs/existing-processors.md.


## Requirements

From composer.json and codebase:
- PHP: ^8.1
- Magento Framework (Magento 2)
- PHP extensions: ext-simplexml, ext-zip, ext-curl
- Composer

Note: A working Magento 2 instance is required to use the module (both CLI and Admin UI).


## Installation

Install via Composer from Packagist:

```bash
composer require crealoz/easy-audit-free
```

Then enable and register the module in Magento:

```bash
php bin/magento module:enable Crealoz_EasyAudit
php bin/magento setup:upgrade
php bin/magento cache:flush
```

Manual installation (for contributors):

1. Clone the repository into your Magento installation under app/code/Crealoz/EasyAudit or use path mapping via composer.
2. Install PHP dependencies if needed (usually handled at the Magento root):
   ```bash
   composer install
   ```
3. Enable the module as above.


## Usage

You can run EasyAudit from the Magento CLI or via the Admin UI.

- CLI command (correct command name):
  ```bash
  php bin/magento crealoz:audit:run \
      --language=en_US \
      --ignored-modules=Vendor_Module1,Vendor_Module2
  ```
  Options:
  - --language (-l): locale to use for messages (default: en_US)
  - --ignored-modules (-i): comma-separated list of modules to exclude

- Admin back-office: see docs/using-admin.md. Screenshots are available in docs/img.


## Configuration and Environment Variables

Configuration can be set in Magento Admin at Stores > Configuration > Crealoz > Easy Audit.

Fields (etc/adminhtml/system.xml):
- Allow PR generation (easy_audit/general/pr_enabled)
- Hash (easy_audit/general/hash)
- Key (easy_audit/general/key)
- Credits left (display only)

Advanced settings (can also be set via app/etc/env.php using DeploymentConfig):
- easy_audit/middleware/host: override API host (default: https://api.crealoz.fr)
- easy_audit/middleware/self_signed: boolean; if true, SSL peer verification is disabled for the middleware client
- easy_audit/middleware/key: middleware key (overrides admin value)
- easy_audit/middleware/hash: middleware hash (overrides admin value)

Notes:
- PR generation uploads selected source data to a dedicated service for automated processing. See in-module notices for details.
- If you’re unsure of host/key/hash values, obtain them from your Crealoz account.
- TODO: Document sandbox/test endpoints if available.


## Features at a Glance

- Detects common Magento anti-patterns (ObjectManager usage, helpers vs ViewModels, SQL in code, heavy classes without proxies, etc.)
- XML/layout checks (cacheable layout handles, preferences, plugins, command registrations)
- Logic-level insights (unused/disabled modules, Block vs ViewModel ratios)
- Database checks (heavy tables, flat catalog)
- PHPCS results export
- Result grids in Admin, downloadable reports, optional patch/ticket generation integration

For in-depth processors list and rationale, see docs/existing-processors.md.


## Patch and Ticket Generation

From the result detail view, a Generate Patch button opens a modal where you can choose relative paths and patch type. Once generated, apply patches incrementally to avoid conflicts.

- Available processors include:
  - Around plugins that should be converted to before/after plugins
  - Proxies for commands

Ticket creation is available for GitHub and Jira; created links appear in the grid and disable the respective button.


## Scripts

Utility scripts are provided in bash/ for development purposes:
- bash/add-headers-php: add a license header to all PHP files
- bash/add-headers-xml: add a license header to all XML files
- bash/add-headers-js: add a license header to all JS files

Composer scripts: none defined in this module’s composer.json.


## Tests

PHPUnit configuration files are provided (phpunit.xml, phpunit.xml.dist). To run tests from the module root:

```bash
./vendor/bin/phpunit
```

Coverage reports (HTML and Clover) will be written under build/ as configured in phpunit.xml.

Notes:
- Unit tests live under Test/Unit; integration tests under Test/Integration.
- Integration tests may require a bootstrapped Magento testing environment and database configuration.
- TODO: Provide fixtures setup instructions for integration tests.


## Project Structure (top-level)

- Api, Model, Service, Processor, Ui, Controller, etc: Magento module code
- etc/: DI, adminhtml, and other Magento configurations
- view/: adminhtml UI components and assets
- Test/: Unit, Integration, and Mocks
- docs/: documentation and screenshots
- bash/: development helpers
- composer.json, registration.php: module metadata and registration
- LICENSE, LICENSE-FR, CONTRIBUTING.md, CODE_OF_CONDUCT.md, SECURITY.md


## License

EasyAudit Free is released under the MIT License. See LICENSE and LICENSE-FR for details.

Note: Certain referenced premium assets (e.g., examples or headers mentioning “EasyAudit Premium”) relate to a separate paid edition and are not part of this free module’s license. TODO: Add a link that compares Free vs Premium features.


## Contributing

Contributions are welcome. Please open issues and submit pull requests. Be sure to run tests before submitting.
