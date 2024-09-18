<?php

namespace Crealoz\EasyAudit\Processor\Files\Logic\Modules;

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
        protected readonly DriverInterface     $driver,
        private readonly FullModuleList        $fullModuleList,
        private readonly ModuleList            $moduleList
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
            $moduleName = $this->getModuleName($file);
            if (in_array($moduleName, $disabledModules)) {
                $moduleList[] = $moduleName;
            }
        }
        return $moduleList;
    }

    /**
     * Looks for the module name in the file path
     *
     * @param string $input
     * @return string
     * @throws FileSystemException
     * @throws \Exception
     */
    public function getModuleName(string $input): string
    {
        $content = $this->driver->fileGetContents($input);
        $xml = new \SimpleXMLElement($content);
        return (string)$xml->module['name'];
    }

    /**
     * Returns all modules
     *
     * @return array
     */
    private function getAllModules(): array
    {
        return $this->fullModuleList->getNames();
    }

    /**
     * Returns enabled modules
     *
     * @return array
     */
    public function getEnabledModules(): array
    {
        return $this->moduleList->getNames();
    }

    /**
     * Returns disabled module names
     *
     * @return array
     */
    private function getDisabledModuleNames(): array
    {
        $fullModuleList = $this->getAllModules();
        $enabledModules = $this->getEnabledModules();

        return array_diff($fullModuleList, $enabledModules);
    }

}