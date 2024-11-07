<?php

namespace Crealoz\EasyAudit\Processor\Files\Di\Plugins;

use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToAfterPluginException;

class AroundToAfter extends AroundChecker
{

    protected function checkAroundMethod($class, $filePath, $aroundMethod): void
    {
        $interior = $this->getFunctionLines($class, $filePath, $aroundMethod);
        $lines = explode("\n", $interior);
        $filteredLines = array_filter($lines, function($line) {
            $line = trim($line);
            return $line !== '' && !preg_match('/^(try|if)/', $line);
        });
        if (isset($filteredLines[1]) && str_contains($filteredLines[1], 'proceed')) {
            throw new AroundToAfterPluginException(__('An around method should not contain $proceed as first statement'), $class);
        }
    }
}