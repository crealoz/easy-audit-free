<?php

namespace Crealoz\EasyAudit\Processor\Files\Di\Plugins;

use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToAfterPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToBeforePluginException;
use Crealoz\EasyAudit\Service\Parser\Functions;
use Magento\Framework\Exception\FileSystemException;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
abstract class AroundChecker
{
    public function __construct(
        protected readonly Functions $functionsParser,
        protected readonly \Magento\Framework\Filesystem\DriverInterface $driver
    )
    {

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
        if (str_contains($fileContent, 'around')) {
            $callable = function($functionName) {
                return str_contains($functionName, 'around');
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
