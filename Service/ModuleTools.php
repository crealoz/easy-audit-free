<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;

class ModuleTools
{

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
        if (!$isVendor && str_contains($filePath, 'vendor')) {
            $isVendor = true;
        }
        try {
            $input = $this->modulePath->getDeclarationXml($filePath, $isVendor);
            return $this->getModuleNameByModuleXml($input);
        } catch (\InvalidArgumentException $e) {
            dump($filePath);
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