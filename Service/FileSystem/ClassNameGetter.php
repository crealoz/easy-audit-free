<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Processor\Files\Logic\Modules\GetModuleConfig;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;

class ClassNameGetter
{

    /**
     * @readonly
     */
    protected DriverInterface $driver;
    /**
     * @readonly
     */
    protected File $io;
    /**
     * @readonly
     */
    private ModulePaths $modulePaths;
    /**
     * @readonly
     */
    private ModuleTools $moduleTools;
    public function __construct(DriverInterface $driver, File $io, ModulePaths $modulePaths, ModuleTools $moduleTools)
    {
        $this->driver = $driver;
        $this->io = $io;
        $this->modulePaths = $modulePaths;
        $this->moduleTools = $moduleTools;
    }

    /**
     * This function will return the full class name from a file path it tries to get t
     * @param $filePathName
     * @return string
     * @throws NotAClassException
     * @throws FileSystemException
     */
    public function getClassFullNameFromFile($filePathName): string
    {
        if ($this->isModuleRegistrationFile($filePathName)) {
            throw new NotAClassException(__('The file %1 is a registration file', $filePathName));
        }
        // We first remove the .php extension
        if ($this->isVendorClass($filePathName)) {
            $fullClassName = $this->getNamespaceForVendorModule($filePathName);
        } elseif ($this->isAppClass($filePathName)) {
            $fullClassName = preg_replace('/.*app\/code/', '', $filePathName);
        } else {
            throw new FileSystemException(__('The file %1 is not in app/code or vendor', $filePathName));
        }
        $fullClassName = str_replace('.php', '', $fullClassName);
        $fullClassName = str_replace('/', '\\', $fullClassName);
        // Class name is the last part of the string
        $parts = explode('\\', $fullClassName);
        $parts = array_map('ucfirst', $parts);
        $className = array_pop($parts);
        $namespace = implode('\\', $parts);
        $namespace = trim($namespace, '\\');

        $fileContent = $this->driver->fileGetContents($filePathName);
        if ($fileContent == null) {
            throw new FileSystemException(__('Could not read the file %1', $filePathName));
        }
        if (strpos($fileContent, 'namespace ' . $namespace) === false) {
            throw new NotAClassException(__('The file %1 does not contain a namespace %2', $filePathName, $namespace));
        }
        if (strpos($fileContent, 'class ' . $className) === false) {
            throw new NotAClassException(__('The file %1 does not contain a class named %2', $filePathName, $className));
        }
        return $fullClassName;
    }

    /**
     * Get the namespace for a vendor module
     * @param string $filePath
     * @return string
     * @throws FileSystemException
     */
    private function getNamespaceForVendorModule(string $filePath): string
    {
        $parts = explode('/', $filePath);
        $moduleXmlPath = $this->modulePaths->getDeclarationXml($filePath, true);
        $moduleName = $this->moduleTools->getModuleNameByModuleXml($moduleXmlPath);
        $namespaceParts = explode('_', $moduleName);
        $namespace = DIRECTORY_SEPARATOR . $namespaceParts[0] . DIRECTORY_SEPARATOR . $namespaceParts[1];
        if (isset($parts[3])) {
            for ($i = 3; $i < count($parts); $i++) {
                $namespace .= DIRECTORY_SEPARATOR . $parts[$i];
            }
        }
        return $namespace;
    }

    /**
     * Check if the file is a module registration file
     * @param string $filePath
     * @return bool
     */
    public function isModuleRegistrationFile(string $filePath): bool
    {
        return strpos($filePath, 'registration.php') !== false;
    }

    /**
     * Check if the class is in the vendor folder
     * @param string $className
     * @return bool
     */
    public function isVendorClass(string $className): bool
    {
        return strpos($className, 'vendor') !== false;
    }

    /**
     * Check if the class is in the app/code folder
     * @param string $className
     * @return bool
     */
    public function isAppClass(string $className): bool
    {
        return strpos($className, 'app/code') !== false;
    }


}