<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;

class ModulePaths
{
    private array $moduleNameByBaseDir = [];
    public function __construct(
        private readonly Filesystem       $filesystem,
        private readonly Json   $jsonSerializer,
    )
    {
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
        $baseDir = $this->getModuleBaseDir($filePath, $isVendor);
        return $baseDir . DIRECTORY_SEPARATOR . 'etc/module.xml';
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
        $installationPath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        $codeDir = $isVendor ? 'vendor' : 'app/code';
        $strippedPath = str_replace($installationPath, '', $filePath);
        $strippedPath = str_replace($codeDir, '', $strippedPath);
        $strippedPath = trim($strippedPath, DIRECTORY_SEPARATOR);
        $parts = explode(DIRECTORY_SEPARATOR, $strippedPath);
        if (!isset($parts[0]) || !isset($parts[1])) {
            return '';
        }
        $vendorName = $parts[0];
        $moduleName = $parts[1];
        $moduleBaseDir = str_replace('//', '/', $installationPath . DIRECTORY_SEPARATOR . $codeDir . DIRECTORY_SEPARATOR . $vendorName . DIRECTORY_SEPARATOR . $moduleName);
        /**
         * Checks if composer.json exists and if it changes the psr4 autoload
         */
        if ($this->filesystem->getDirectoryReadByPath($moduleBaseDir)->isExist('composer.json')) {
            $composerJson = $this->jsonSerializer->unserialize($this->filesystem->getDirectoryReadByPath($moduleBaseDir)->readFile('composer.json'));
            if (
                isset($composerJson['autoload']['psr-4']) &&
                isset($composerJson['autoload']['psr-4'][$vendorName . '\\' . $moduleName . '\\']) &&
                $composerJson['autoload']['psr-4'][$vendorName . '\\' . $moduleName . '\\'] !== ''
            ) {
                $moduleBaseDir = $moduleBaseDir . DIRECTORY_SEPARATOR . $composerJson['autoload']['psr-4'][$vendorName . '\\' . $moduleName . '\\'];
            }
        }
        return $moduleBaseDir;
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