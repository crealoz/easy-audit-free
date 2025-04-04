<?php


namespace Crealoz\EasyAudit\Processor\Files\Logic\Modules;

use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Exception\Processor\Modules\EnabledStatusIrretrievableException;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class checks if a module is enabled or not. It will try to get the active status of a module. If the module has a Config.php file, it will try to invoke the isEnabled
 * or isActive functions. If the module does not have a Config.php file, it will try to loop through non-generic files
 * and try to invoke isEnabled or isActive functions. If none is found, it will throw a EnabledStatusIrretrievableException.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class CheckEnabled
{

    private array $storeIds = [];

    public function __construct(
        protected readonly DriverInterface     $driver,
        private readonly StoreManagerInterface $storeManager,
        private readonly ClassNameGetter       $classNameGetter,
        private readonly ScopeConfigInterface  $scopeConfig
    )
    {
    }

    /**
     * Tries to get the active status of a module. If the module has a Config.php file, it will try to invoke the isEnabled
     * or isActive functions. If the module does not have a Config.php file, it will try to loop through non-generic files
     * and try to invoke isEnabled or isActive functions. If none is found, it will throw a EnabledStatusIrretrievableException.
     *
     * @param string $moduleDeclarationPath
     * @return bool
     * @throws EnabledStatusIrretrievableException
     */
    public function isModuleEnabled(string $moduleDeclarationPath): bool
    {
        $configFile = $this->getModuleConfigFile($moduleDeclarationPath);
        $nonGenericProcessed = false;
        try {
            if ($this->driver->isExists($configFile)) {
                return $this->invokeGenericEnabledFunctions($configFile);
            } else {
                $nonGenericProcessed = true;
                return $this->manageNonGenericFiles($moduleDeclarationPath);
            }
        } catch (FileSystemException|\ReflectionException|EnabledStatusIrretrievableException $e) {
            if (!$nonGenericProcessed) {
                return $this->manageNonGenericFiles($moduleDeclarationPath);
            } else {
                throw new EnabledStatusIrretrievableException(__('The module does not have an isEnabled or isActive method'));
            }
        }
    }

    /**
     * Returns the module configuration file path
     *
     * @param string $moduleDeclarationPath
     * @return string
     */
    private function getModuleConfigFile(string $moduleDeclarationPath): string
    {
        $modulePath = $this->driver->getParentDirectory($moduleDeclarationPath);
        $modulePath = str_replace('/etc', '', $modulePath);
        return $modulePath . '/Model/Config.php';
    }

    /**
     * Checks if isEnabled or isActive functions exist in the module's Config.php file if it exists, it tries to invoke
     * and return the result for each of active stores.
     *
     * @param string $configFile
     * @return bool
     * @throws EnabledStatusIrretrievableException
     * @throws \ReflectionException
     * @throws FileSystemException
     * @throws NotAClassException
     */
    private function invokeGenericEnabledFunctions(string $configFile): bool
    {
        $isEnabled = false;
        $className = $this->classNameGetter->getClassFullNameFromFile($configFile);
        $class = new \ReflectionClass($className);
        try {
            $method = $class->getMethod('isEnabled');
        } catch (\ReflectionException $e) {
            $method = $class->getMethod('isActive');
        }
        $stores = $this->getStoreIds();
        $configPath = $this->getConfigPathFromMethod($method, $configFile, $className);
        foreach ($stores as $store) {
            $isEnabled = $this->scopeConfig->isSetFlag($configPath, ScopeInterface::SCOPE_STORE, $store);
            if ($isEnabled) {
                break;
            }
        }
        return $isEnabled;
    }

    /**
     * Gets the config path from the method by reading the method's content and extracting the path from it.
     *
     * @param \ReflectionMethod $method
     * @param string $configFile
     * @param string $className
     * @return string
     * @throws EnabledStatusIrretrievableException|FileSystemException
     */
    private function getConfigPathFromMethod(\ReflectionMethod $method, string $configFile, string $className): string
    {
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $fileContent = $this->driver->fileGetContents($configFile);
        $methodContent = array_slice(explode(PHP_EOL, $fileContent), $startLine, $endLine - $startLine);
        $methodContent = implode('', $methodContent);
        $pattern = '/\b(?:isSetFlag|getValue)\s*\(\s*([^,\)\}\s]+)/';
        preg_match($pattern, $methodContent, $matches);
        if (!isset($matches[1])) {
            throw new EnabledStatusIrretrievableException(__('The module does not have an isEnabled or isActive method'));
        }
        $configPath = $matches[1];
        if (str_contains($configPath, '::')) {
            $constantParts = explode('::', $configPath);
            $constantClassName = $constantParts[0] === 'self' ? $className : $constantParts[0];
            $constantName = $constantParts[1];
            $constant = $constantClassName . '::' . $constantName;
            // If constant is private, we will try to get the value of it.
            if (defined(trim($constant))) {
                $configPath = constant(trim($constant));
            } else {
                // We will try to get the value of the constant with a preg_match in $fileContent
                if ($constantClassName === $className) {
                    $constantFileContent = $fileContent;
                } else {
                    $constantFileContent = $this->driver->fileGetContents($constantClassName);
                }
                preg_match('/const\s+' . $constantName . '\s*=\s*(.*?);/', $constantFileContent, $matches);
                $configPath = $matches[1];
            }
        }
        return $configPath;
    }

    /**
     * Tries to loop through non-generic files of a module and tries to invoke isEnabled or isActive functions. If none
     * is found, it will throw a EnabledStatusIrretrievableException.
     *
     * @param $moduleDeclarationPath
     * @return bool
     * @throws EnabledStatusIrretrievableException
     */
    private function manageNonGenericFiles($moduleDeclarationPath): bool
    {
        $found = false;
        $result = false;
        try {
            $nonGenericFiles = $this->getNonGenericFilePaths($moduleDeclarationPath);
            foreach ($nonGenericFiles as $file) {
                try {
                    if ($this->driver->isExists($file)) {
                        $result = $this->invokeGenericEnabledFunctions($file);
                        $found = true;
                    }
                } catch (EnabledStatusIrretrievableException|\ReflectionException|FileSystemException $e) {
                    // Do nothing and continue
                }
            }
        } catch (FileSystemException $e) {
            // Do nothing
        }
        if (!$found) {
            throw new EnabledStatusIrretrievableException(__('The module does not have an isEnabled or isActive method'));
        }
        return $result;
    }

    /**
     * Gets the module path from the module declaration path (etc/module.xml) and looks for isActive or isEnabled functions.
     * Returns the non-generic file paths of a module.
     *
     * @param string $moduleDeclarationPath
     * @return array
     * @throws FileSystemException
     */
    private function getNonGenericFilePaths(string $moduleDeclarationPath): array
    {
        $modulePath = $this->driver->getParentDirectory($moduleDeclarationPath);
        $modulePath = str_replace('etc/', '', $modulePath);
        // First we look for files that look like Config.php
        $files = $this->driver->readDirectory($modulePath);
        $nonGenericFiles = [];
        foreach ($files as $file) {
            if (str_contains((string) $file, 'Config.php')) {
                $nonGenericFiles[] = $file;
            }
        }
        return $nonGenericFiles;
    }

    /**
     * Returns all store ids
     *
     * @return array
     */
    private function getStoreIds(): array
    {
        if ($this->storeIds === []) {
            $stores = $this->storeManager->getStores();
            foreach ($stores as $store) {
                $this->storeIds[] = $store->getId();
            }
        }
        return $this->storeIds;
    }
}
