<?php
/**
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */

namespace Crealoz\EasyAudit\Service\PrManager;

use Crealoz\EasyAudit\Service\Classes\ArgumentTypeChecker;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;

class NoProxyForHeavyClasses implements BodyPreparerInterface
{
    public function __construct(
        protected readonly ModulePaths $modulePaths,
        protected readonly ArgumentTypeChecker $argumentTypeChecker
    )
    {
    }

    /**
     * Prepare the body with the given result and patch type.
     *
     * @param $result
     * @param string $patchType
     * @param string $relativePath
     * @return array
     * @throws \ReflectionException
     */
    public function prepare($result, $patchType, $relativePath = ''): array
    {
        $body = [
            'type' => $result->getProcessor(),
            'patch_type' => $patchType,
        ];
        $files = [];
        foreach ($result->getEntries() as $entry) {
            foreach ($entry->getSubentries() as $subentry) {
                $commandClass = str_replace(["\r", "\n"], '', $subentry->getSubentry());
                $reflection = new \ReflectionClass($commandClass);
                $diFile = $this->modulePaths->getDiXml($reflection->getFileName())['general'];
                $diFilePath = str_replace($relativePath, '', $diFile);
                if (!isset($files[$diFilePath])) {
                    $fileContent = simplexml_load_file($diFile);
                    $files[$diFilePath] = [
                        'content' => $fileContent->asXML(),
                        'heavy_classes' => []
                    ];
                }
                $constructorArguments = [];
                $constructor = $reflection->getConstructor();
                if ($constructor) {
                    $parameters = $constructor->getParameters();
                    foreach ($parameters as $parameter) {
                        if ($parameter->getType()->getName() !== $entry && !$this->argumentTypeChecker->isArgumentSession($parameter)) {
                            continue;
                        }
                        $constructorArguments[$parameter->getName()] = $parameter->getType()->getName() . '\Proxy';
                    }
                }
                $files[$diFilePath]['heavy_classes'][$commandClass] = $constructorArguments;
            }
        }
        $body['files'] = $files;
        return $body;
    }
}
