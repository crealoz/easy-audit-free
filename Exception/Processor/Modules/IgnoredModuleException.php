<?php

namespace Crealoz\EasyAudit\Exception\Processor\Modules;

use Magento\Framework\Exception\LocalizedException;

class IgnoredModuleException extends LocalizedException
{
    public function __construct(string $moduleName)
    {
        parent::__construct(__('The module %1 is ignored during the audit', $moduleName));
    }
}