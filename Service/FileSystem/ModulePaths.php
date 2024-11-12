<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Magento\Framework\Filesystem;

class ModulePaths
{
    public function __construct(
        private readonly Filesystem       $filesystem,
    )
    {
    }

    public function getDeclarationXml(string $filePath, bool $isVendor = false): string
    {
        $parts = explode('/', $filePath);
        $moduleXmlPath = $parts[0] . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2] . DIRECTORY_SEPARATOR . $parts[3] . DIRECTORY_SEPARATOR . 'etc/module.xml';
        if ($isVendor) {
            $moduleXmlPath = $parts[0] . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2] . DIRECTORY_SEPARATOR . 'etc/module.xml';
        }
        return $moduleXmlPath;
    }

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

    public function getModuleBaseDir(string $filePath, bool $isVendor = false): string
    {
        $parts = explode('/', $filePath);
        $baseDir = $parts[0] . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2] . DIRECTORY_SEPARATOR . $parts[3];
        if ($isVendor) {
            $baseDir = $parts[0] . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2];
        }
        return $baseDir;
    }
}