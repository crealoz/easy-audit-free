<?php
/**
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */

namespace Crealoz\EasyAudit\Exception\Processor\Modules;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception thrown when a module is ignored during the audit
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class IgnoredModuleException extends LocalizedException
{
    public function __construct(string $moduleName)
    {
        parent::__construct(__('The module %1 is ignored during the audit', $moduleName));
    }
}
