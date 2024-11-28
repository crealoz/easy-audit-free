<?php

namespace Crealoz\EasyAudit\Processor\Files\Logic\Modules;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;

/**
 * Class GetConfigUnactive
 *
 * This processor is responsible for finding the modules that are not active in the app/etc/config.php file.
 */
class GetModuleConfig
{

    public function __construct(
        private readonly ModuleTools $moduleTools
    )
    {
    }

    /**
     * @throws FileSystemException
     */
    public function process(array $input): array
    {
        // Get the modules in app/etc/config.php
        $moduleList = [];
        $disabledModules = $this->getDisabledModuleNames();
        foreach ($input as $file) {
            $moduleName = $this->moduleTools->getModuleNameByModuleXml($file);
            if (in_array($moduleName, $disabledModules)) {
                $moduleList[] = $moduleName;
            }
        }
        return $moduleList;
    }

    /**
     * Returns disabled module names
     *
     * @return array
     */
    private function getDisabledModuleNames(): array
    {
        $fullModuleList = $this->moduleTools->getAllModules();
        $enabledModules = $this->moduleTools->getEnabledModules();

        return array_diff($fullModuleList, $enabledModules);
    }

}