<?php


namespace Crealoz\EasyAudit\Service\PrManager;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;

class NoProxyUsedInCommands implements BodyPreparerInterface
{

    public function __construct(
        private readonly ModulePaths $modulePaths
    )
    {

    }

    /**
     * The `$files` array is expected to have the following structure:
     *
     * $files = [
     *     [
     *         'path/to/di.xml' => [
     *             'content' => 'DI file content as string',
     *             'commands' => [
     *                  'CommandClassName' => [
     *                      'proxyName1' => 'ProxyClassName1',
     *                      'proxyName2' => 'ProxyClassName2',
     *                 ]
     *             ]
     *         ]
     *     ],
     *     // More files can be added in the same structure
     * ];
     *
     * Each file in the array should have:
     * - `di`: An associative array with a `content` key containing the DI file content as a string. The `path` key indicates the path to di file.
     * - `commands`: An array of commands where the key is the command class name and the value is an array of proxies.
     * @param $result
     * @param string $patchType
     * @param string $relativePath
     * @return array
     */
    public function prepare($result, $patchType, $relativePath = ''): array
    {
        $body = [
            'type' => $result->getProcessor(),
            'patch_type' => $patchType,
        ];
        $files = [];
        foreach ($result->getEntries() as $entry) {
            $commandClass = $entry->getEntry();
            $reflection = new \ReflectionClass($commandClass);
            $diFile = $this->modulePaths->getDiXml($reflection->getFileName())['general'];
            $diFilePath = str_replace($relativePath, '', $diFile);
            if (!isset($files[$diFilePath])) {
                $fileContent = simplexml_load_file($diFile);
                $files = [
                    $diFilePath => [
                        'content' => $fileContent->asXML(),
                        'commands' => []
                    ]
                ];
            }
            $constructorArguments = [];
            $constructor = $reflection->getConstructor();
            if ($constructor) {
                $parameters = $constructor->getParameters();
                foreach ($parameters as $parameter) {
                    // Check if the parameter is built-in type
                    if ($parameter->getType()->isBuiltin()) {
                        continue;
                    }
                    $constructorArguments[$parameter->getName()] = $parameter->getType()->getName() . '\Proxy';
                }
            }
            $files[$diFilePath]['commands'][$commandClass] = $constructorArguments;
        }
        $body['files'] = $files;
        return $body;
    }
}
