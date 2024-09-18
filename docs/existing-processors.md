# EasyAudit Processors

This document lists all the processors used in the EasyAudit project, organized by their respective categories, along with a brief description of what each processor does.

## XML Processors

### DI Processors
- **Plugins**: `Crealoz\EasyAudit\Processor\Files\\Di\Plugins`
  - **Description**: Analyzes and audits the usage of plugins in the dependency injection configuration. Checks for plugins on Classes that should not be plugged. Incorrect usage of around plugins...
- **Preferences**: `Crealoz\EasyAudit\Processor\Files\\Di\Preferences`
  - **Description**: Checks and audits the multiple usage of preferences for the same class in app/code/.
- **Commands**: `Crealoz\EasyAudit\Processor\Files\\Di\Commands`
  - **Description**: Audits the command configurations in the dependency injection XML files and checks for the usage of proxy.

### Layout Processors
- **Cacheable**: `Crealoz\EasyAudit\Processor\Files\\View\Cacheable`
  - **Description**: Ensures that layout XML files are properly configured to be cacheable.

## PHP Processors

### Helpers
- **General**: `Crealoz\EasyAudit\Processor\Files\\Code\Helpers`
  - **Description**: Audits the usage of helper classes in the PHP codebase.

### PHP Code Processors
- **SQL**: `Crealoz\EasyAudit\Processor\Files\\Code\HardWrittenSQL`
  - **Description**: Detects and audits hard-written SQL queries in the PHP code.
- **Object Manager**: `Crealoz\EasyAudit\Processor\Files\\Code\UseOfObjectManager`
  - **Description**: Checks for direct usage of the Object Manager in the PHP code.

## Logic Processors

### Block View Model Ratio
- **Ratio**: `Crealoz\EasyAudit\Processor\Files\\Code\BlockViewModelRatio`
  - **Description**: Analyzes the ratio of blocks to view models in the codebase to ensure a balanced architecture.

### Local Unused Modules
- **Config PHP**: `Crealoz\EasyAudit\Processor\Files\\Logic\LocalUnusedModules`
  - **Description**: Identifies and audits local modules that are defined but not used in the configuration.

### Vendor Unused Modules
- **Config PHP**: `Crealoz\EasyAudit\Processor\Files\\Logic\VendorUnusedModules`
  - **Description**: Detects vendor modules that are defined but not used in the configuration.
- **Active Disabled**: `Crealoz\EasyAudit\Processor\Files\\Logic\VendorDisabledModules`
  - **Description**: Audits vendor modules that are disabled but still present in the configuration.