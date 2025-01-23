<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;

class ModuleTools
{

    /**
     * @readonly
     */
    protected DriverInterface $driver;
    /**
     * @readonly
     */
    private FullModuleList $fullModuleList;
    /**
     * @readonly
     */
    private ModuleList $moduleList;
    /**
     * @readonly
     */
    private ModulePaths $modulePath;
    public function __construct(DriverInterface $driver, FullModuleList    $fullModuleList, ModuleList        $moduleList, ModulePaths       $modulePath)
    {
        $this->driver = $driver;
        $this->fullModuleList = $fullModuleList;
        $this->moduleList = $moduleList;
        $this->modulePath = $modulePath;
    }


    /**
     * Looks for the module name in the file path
     *
     * @param string $input
     * @return string
     * @throws FileSystemException
     * @throws \InvalidArgumentException
     */
    public function getModuleNameByModuleXml(string $input): string
    {
        if (!$this->driver->isExists($input)) {
            throw new \InvalidArgumentException(__('File not found: %1', $input));
        }
        $content = $this->driver->fileGetContents($input);
        $xml = new \SimpleXMLElement($content);
        return (string)$xml->module['name'];
    }

    /**
     * @throws FileSystemException
     */
    public function getModuleNameByAnyFile(string $filePath, bool $isVendor = false): string
    {
        if ($filePath === '') {
            throw new \InvalidArgumentException(__('File path is empty'));
        }
        if (!$isVendor && strpos($filePath, 'vendor') !== false) {
            $isVendor = true;
        }
        try {
            $input = $this->modulePath->getDeclarationXml($filePath, $isVendor);
            return $this->getModuleNameByModuleXml($input);
        } catch (\InvalidArgumentException $e) {
            $input = $this->modulePath->getDeclarationXml($filePath, !$isVendor);
            return $this->getModuleNameByModuleXml($input);
        }
    }

    /**
     * Returns all modules
     *
     * @return array
     */
    public function getAllModules(): array
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
}