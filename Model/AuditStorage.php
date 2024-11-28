<?php

namespace Crealoz\EasyAudit\Model;

use Magento\Framework\Module\Dir\Reader;

class AuditStorage
{
    private array $ignoredModules = [];

    public function __construct(
        private readonly Reader              $moduleReader
    )
    {}

    public function getIgnoredModules(): array
    {
        return $this->ignoredModules;
    }

    /**
     * @param array $ignoredModules
     *
     * @throws \InvalidArgumentException
     */
    public function setIgnoredModules(array $ignoredModules): void
    {
        foreach ($ignoredModules as $module) {
            $this->ignoredModules[$module] = $this->moduleReader->getModuleDir('', $module);
        }
    }

    /**
     * Check if a module is ignored by its name
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function isModuleIgnored(string $moduleName): bool
    {
        return isset($this->ignoredModules[$moduleName]);
    }
}