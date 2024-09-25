# EasyAudit Processors

This document lists all the processors used in the EasyAudit project, organized by their respective categories, along with a brief description of what each processor does.

## XML Processors

### DI Processors
- **Plugins**:
  - **Description**: Analyzes and audits the usage of plugins in the dependency injection configuration. Checks for plugins on Classes that should not be plugged. Incorrect usage of around plugins...
- **Preferences** (premium):
  - **Description**: Checks and audits the multiple usage of preferences for the same class in app/code/.
- **Commands** (premium):
  - **Description**: Audits the command configurations in the dependency injection XML files and checks for the usage of proxy.

### Layout Processors
- **Cacheable**:
  - **Description**: Ensures that layout XML files are properly configured to be cacheable.

## PHP Processors

### Helpers
- **General** (premium):
  - **Description**: Audits the usage of helper classes in the PHP codebase.

### PHP Code Processors
- **SQL** (premium):
  - **Description**: Detects and audits hard-written SQL queries in the PHP code.
- **Object Manager** (premium):
  - **Description**: Checks for direct usage of the Object Manager in the PHP code.

## Logic Processors

### Block View Model Ratio
- **Ratio**:
  - **Description**: Analyzes the ratio of blocks to view models in the codebase to ensure a balanced architecture.

### Local Unused Modules
- **Config PHP**:
  - **Description**: Identifies and audits local modules that are defined but not used in the configuration.

### Vendor Unused Modules
- **Config PHP** (premium):
  - **Description**: Detects vendor modules that are defined but not used in the configuration.
- **Active Disabled** (premium):
  - **Description**: Audits vendor modules that are disabled but still present in the configuration.