# EasyAudit Module

[![Latest Stable Version](https://img.shields.io/packagist/v/crealoz/easy-audit-free.svg?style=flat-square)](https://packagist.org/packages/crealoz/easy-audit-free)
[![License: MIT](https://img.shields.io/github/license/crealoz/easy-audit-free.svg?style=flat-square)](./LICENSE)
[![Packagist](https://img.shields.io/packagist/dt/crealoz/easy-audit-free.svg?style=flat-square)](https://packagist.org/packages/crealoz/easy-audit-free/stats)
[![Packagist](https://img.shields.io/packagist/dm/crealoz/easy-audit-free.svg?style=flat-square)](https://packagist.org/packages/crealoz/easy-audit-free/stats)
[![codecov](https://codecov.io/gh/crealoz/easy-audit-free/graph/badge.svg?token=CKH0L0G395)](https://codecov.io/gh/crealoz/easy-audit-free)

This module is designed to provide auditing capabilities for Magento applications.

## What is EasyAudit?

EasyAudit is a Magento module that provides auditing capabilities for Magento applications. It is designed to help 
developers and/or website owners identify potential issues in their codebase and improve the overall quality of their
Magento applications.

## Features

For more details on the processors used in the EasyAudit project, refer to the [Existing Processors](docs/existing-processors.md).

## Installation

The package is available on Packagist, so you can install it via Composer:

```bash
composer require crealoz/easy-audit-free
```

If you want to install the module manually (for participation), follow these steps:

1. Clone the repository:
```bash
git clone git@github.com:crealoz/easy-audit.git
```
2. Navigate to the project directory:
```bash
cd easy-audit
```
3. Install the module via Composer:
```bash
composer install
```

## Usage

Audit can be run using magento CLI command:

```bash
php bin/magento crealoz:audit:run
```
or using the [back-office](docs/using-admin.md).

## Settings

The settings are available in the admin panel. You can set the API key for the patch generation and tickes generation.

![](docs/img/system-config.png)


***Note*** : None of the configuration is compulsory. You can use the module without any configuration but related
functionalities will not be available.

## How to use

A new column is added to the admin grid.

![](docs/img/request-grid.png)

You can click on the view link to see the details of the audit. A grid is displayed with the results and you
can see at a glance the different problems and the attributed severity.

![](docs/img/results-grid.png)

### Patch generation
Once you are in the detailed view, there will be a "generate patch" button. This button will open a modal to
generate the patch. You can choose the path that neeeds to be removed from absolute one and the type of patch.

![](docs/img/result-view-patch-generation.png)

![](docs/img/patch-generation-modal.png)

The patch will be generated and delivered as soon as possible.

![](docs/img/result-view-patch-generated.png)

The only thing you need to do is to apply the patch to your code. Better do it one by one to avoid any conflict.

#### Available processors

* Around plugins that should be converted to before or after plugins
* Proxies for commands

### Ticket generation

You can generate tickets for GitHub and jira.

Click on any button. A ticket will be generated and the link will be displayed first in the info message and later
in the grid. If an issue has already been created, the button will be deactivated.

![](docs/img/result-view-ticket-creation.png)

The column "bug tracker" has now the link for results where a ticket has been created.

![](docs/img/result-grid-with-bugtracker-links.png)

## How to add a new audit subject?

### General considerations

There is a single entry point for the audit process, which is the `\Crealoz\EasyAudit\Service\Audit` class. This class is
responsible for running the audit process and handling the audit results. The audit process will loop through the list of
types of audit subjects and run the audit processes for each of them.

### Create a new type of audit subject

For the moment, the audit process is divided into two types: `xml`, `php` and `logic`. If you want to create a new type, you need
to create a new class that implements the `TypeInterface` interface. The class should be located in the `Service\Type`
directory. The new class can extend the `AbstractType` class, which provides a default implementation for the `TypeInterface`.

The new class should be registered in the `di.xml` file, in the `typeMapping` arguments of the class `Crealoz\EasyAudit\Service\Type\TypeFactory`.
Please note that the entry in the `typeMapping` arguments should be in the format `type => class` and type will be used
to identify the type of the audit subject for the `processors` of `\Crealoz\EasyAudit\Service\Audit`.

### Create a new audit subject

Create a new class that implements `ProcessorInterface`. The class should be located in the `Service\Processor` directory.
It can extend the `AbstractProcessor` class, which provides a default implementation for the `ProcessorInterface` methods.

### Register the new audit subject

In `di.xml` file, add a new `item` node to the `processor` arguments of the class `Crealoz\EasyAudit\Service\Audit`.
Please note that the processors are divided by _types_ (e.g. : di, view...) and if you want to create a new type. The 
logic have to be implemented and the new type have to implement the `Crealoz\EasyAudit\Service\Processor\ProcessorInterface`
interface.

## Adding a New File Getter

To add a new file getter similar to `DiXmlGetter`, follow these steps:

### Step 1: Define the Virtual Type in `di.xml`

Add a new virtual type definition in your `di.xml` file. Replace `NewFileGetter` with your desired name, and update the `path` and `pattern` arguments as needed.

```xml
<virtualType name="Crealoz\EasyAudit\Service\FileSystem\NewFileGetter" type="Crealoz\EasyAudit\Service\FileSystem\FileGetter">
    <arguments>
        <argument name="path" xsi:type="string">your/path/here</argument>
        <argument name="pattern" xsi:type="string">/your-pattern-here/</argument>
    </arguments>
</virtualType>
```

### Step 2: Register the New File Getter in `FileGetterFactory`

Add your new file getter to the `fileGetters` array in `di.xml`:

```xml
<type name="Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory">
    <arguments>
        <argument name="fileGetters" xsi:type="array">
            <item name="newfilegetter" xsi:type="string">Crealoz\EasyAudit\Service\FileSystem\NewFileGetter</item>
        </argument>
    </arguments>
</type>
```

## Contributing

Contributions are welcome. Please make sure to update tests as appropriate.
