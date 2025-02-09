<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;

class ModuleTools
{
    private array $moduleNamesByModuleXml = [];

    private array $moduleNamesByFile = [];

    public function __construct(
        protected readonly DriverInterface $driver,
        private readonly FullModuleList    $fullModuleList,
        private readonly ModuleList        $moduleList,
        private readonly ModulePaths       $modulePath
    )
    {
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
        if (isset($this->moduleNamesByModuleXml[$input])) {
            return $this->moduleNamesByModuleXml[$input];
        }
        if (!$this->driver->isExists($input)) {
            dump('file does not exist');
            dump($input);
            throw new \InvalidArgumentException(__('File not found: %1', $input));
        }
        $content = $this->driver->fileGetContents($input);
        $xml = new \SimpleXMLElement($content);
        $moduleName = (string)$xml->module['name'];
        if ($moduleName === '') {
            throw new \InvalidArgumentException(__('Module name not found in %1', $input));
        }
        $this->moduleNamesByModuleXml[$input] = $moduleName;
        return $moduleName;
    }

    /**
     * @throws FileSystemException
     */
    public function getModuleNameByAnyFile(string $filePath, bool $isVendor = false): string
    {
        if (isset($this->moduleNamesByFile[$filePath])) {
            return $this->moduleNamesByFile[$filePath];
        }
        if ($filePath === '') {
            throw new \InvalidArgumentException(__('File path is empty'));
        }
        if (!$isVendor && str_contains($filePath, 'vendor')) {
            $isVendor = true;
        }
        try {
            $input = $this->modulePath->getDeclarationXml($filePath, $isVendor);
            $moduleName = $this->getModuleNameByModuleXml($input);
        } catch (\InvalidArgumentException $e) {
            $input = $this->modulePath->getDeclarationXml($filePath, !$isVendor);
            $moduleName = $this->getModuleNameByModuleXml($input);
        }
        if ($moduleName === '') {
            throw new \InvalidArgumentException(__('Module name not found in %1', $filePath));
        }
        $this->moduleNamesByFile[$filePath] = $moduleName;
        return $moduleName;
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