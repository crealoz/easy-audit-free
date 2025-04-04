<?php


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
