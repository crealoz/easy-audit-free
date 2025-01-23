<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class ModulePaths
{
    /**
     * @readonly
     */
    private Filesystem $filesystem;
    public function __construct(Filesystem       $filesystem)
    {
        $this->filesystem = $filesystem;
    }
    /**
     * Get the path to the module.xml file of a module
     *
     * @param string $filePath
     * @param bool $isVendor
     * @return string
     */
    public function getDeclarationXml(string $filePath, bool $isVendor = false): string
    {
        $filePath = $this->removeBaseNameFromPath($filePath);
        $parts = explode('/', $filePath);
        if (!isset($parts[3])) {
            return '';
        }
        if (!isset($parts[2])) {
            return '';
        }
        $moduleXmlPath = $parts[0] . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2] . DIRECTORY_SEPARATOR . $parts[3] . DIRECTORY_SEPARATOR . 'etc/module.xml';
        if ($isVendor) {
            $moduleXmlPath = $parts[0] . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2] . DIRECTORY_SEPARATOR . 'etc/module.xml';
        }
        return $moduleXmlPath;
    }

    /**
     * Get the different di.xml files of a module
     *
     * @param string $filePath
     * @param bool $isVendor
     * @return array
     */
    public function getDiXml(string $filePath, bool $isVendor = false): array
    {
        $baseDir = $this->getModuleBaseDir($filePath, $isVendor);
        $diXmlPath = [];
        if ($this->filesystem->getDirectoryReadByPath($baseDir . DIRECTORY_SEPARATOR . 'etc')->isExist('di.xml')) {
            $diXmlPath['general'] = $baseDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'di.xml';
        }
        if ($this->filesystem->getDirectoryReadByPath($baseDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'frontend')->isExist('di.xml')) {
            $diXmlPath['frontend'] = $baseDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'di.xml';
        }
        if ($this->filesystem->getDirectoryReadByPath($baseDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'adminhtml')->isExist('di.xml')) {
            $diXmlPath['adminhtml'] = $baseDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'adminhtml' . DIRECTORY_SEPARATOR . 'di.xml';
        }
        return $diXmlPath;
    }

    /**
     * @param string $filePath
     * @param bool $isVendor
     * @return string
     */
    public function getFrontendPath(string $filePath, bool $isVendor = false): string
    {
        return $this->getModuleBaseDir($filePath, $isVendor) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'frontend';
    }

    /**
     * Get the base directory of a module
     *
     * @param string $filePath
     * @param bool $isVendor
     * @return string
     */
    public function getModuleBaseDir(string $filePath, bool $isVendor = false): string
    {
        $parts = explode('/', $filePath);
        $baseDir = $parts[0] . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2] . DIRECTORY_SEPARATOR . $parts[3];
        if ($isVendor) {
            $baseDir = $parts[0] . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2];
        }
        return $baseDir;
    }

    /**
     * Remove the vendor or app part of a path
     *
     * @param string $path
     * @return string
     */
    public function stripVendorOrApp(string $path): string
    {
        $path = $this->removeBaseNameFromPath($path);
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        if (isset($pathParts[0]) && in_array($pathParts[0], ['vendor', 'app'])) {
            $offset = $pathParts[0] === 'vendor' ? 1 : 2;
            return implode(DIRECTORY_SEPARATOR, array_slice($pathParts, $offset));
        }
        return $path;
    }

    /**
     * Remove the base name of a path
     *
     * @param string $path
     * @return string
     */
    public function removeBaseNameFromPath(string $path): string
    {
        $magentoInstallationPath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        return str_replace($magentoInstallationPath, '', $path);
    }
}