<?php

namespace Crealoz\EasyAudit\Processor\Files\Di\Plugins;

use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToAfterPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToBeforePluginException;
use Crealoz\EasyAudit\Service\Parser\Functions;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
abstract class AroundChecker
{
    /**
     * @readonly
     */
    protected Functions $functionsParser;
    /**
     * @readonly
     */
    protected DriverInterface $driver;
    public function __construct(Functions $functionsParser, DriverInterface $driver)
    {
        $this->functionsParser = $functionsParser;
        $this->driver = $driver;
    }

    /**
     * @throws \ReflectionException
     * @throws FileSystemException
     * @throws AroundToBeforePluginException
     * @throws AroundToAfterPluginException
     */
    public function execute($class): void
    {
        $filePath = (new \ReflectionClass($class))->getFileName();
        $fileContent = $this->driver->fileGetContents($filePath);
        if (strpos($fileContent, 'around') !== false) {
            $callable = function($functionName) {
                return strpos($functionName, 'around') !== false;
            };
            $aroundMethods = [];
            foreach (get_class_methods($class) as $methodName) {
                if ($callable($methodName)) {
                    $aroundMethods[] = $methodName;
                }
            }
            foreach ($aroundMethods as $aroundMethod) {
                $this->checkAroundMethod($class, $filePath, $aroundMethod);
            }
        }
    }

    protected function getFunctionLines($class, $filePath, $aroundMethod): string
    {
        $functionContent = $this->functionsParser->getFunctionContent($class, $filePath, $aroundMethod);
        preg_match('/\{(.*)\}/s', $functionContent, $matches);
        return trim($matches[1]);
    }

    /**
     * @param $class
     * @param $filePath
     * @param $aroundMethod
     * @return void
     * @throws AroundToBeforePluginException|AroundToAfterPluginException
     */
    abstract protected function checkAroundMethod($class, $filePath, $aroundMethod): void;

}
