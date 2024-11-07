<?php

namespace Crealoz\EasyAudit\Processor\Files\Di\Plugins;

use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToBeforePluginException;

class AroundToBefore extends AroundChecker
{
    /**
     * @param $class
     * @param $filePath
     * @param $aroundMethod
     * @return void
     * @throws AroundToBeforePluginException
     */
    protected function checkAroundMethod($class, $filePath, $aroundMethod): void
    {
        $interior = $this->getFunctionLines($class, $filePath, $aroundMethod);
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