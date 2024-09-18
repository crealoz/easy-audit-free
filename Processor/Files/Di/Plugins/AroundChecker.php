<?php

namespace Crealoz\EasyAudit\Processor\Files\Di\Plugins;

use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToAfterPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToBeforePluginException;
use Crealoz\EasyAudit\Service\Parser\Functions;
use Magento\Framework\Exception\FileSystemException;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class AroundChecker
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

    /**
     * @throws AroundToAfterPluginException
     * @throws AroundToBeforePluginException
     */
    protected function checkAroundMethod($class, $filePath, $aroundMethod): void
    {
        $functionContent = $this->functionsParser->getFunctionContent($class, $filePath, $aroundMethod);
        preg_match('/\{(.*)\}/s', $functionContent, $matches);
        $interior = trim($matches[1]);

        $lines = explode("\n", $interior);
        $filteredLines = array_filter($lines, function($line) {
            $line = trim($line);
            return $line !== '' && !preg_match('/^(try|if)/', $line);
        });
        if (isset($filteredLines[1]) && str_contains($filteredLines[1], 'proceed')) {
            throw new AroundToAfterPluginException(__('An around method should not contain $proceed as first statement'), $class);
        }
        $lines = array_reverse(explode("\n", $interior));
        $lastReturn = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (str_contains($line, 'return')) {
                $lastReturn = $line;
                break;
            }
        }
        if (str_contains($lastReturn, '$proceed')) {
            throw new AroundToBeforePluginException(__('An around method should not return $proceed as last statement'), $class);
        }
    }
}
