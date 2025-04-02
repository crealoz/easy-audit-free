<?php


namespace Crealoz\EasyAudit\Processor\Files\Di;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Processor\Files\AbstractXmlProcessor;
use Crealoz\EasyAudit\Service\Audit;

class Commands extends AbstractXmlProcessor implements FileProcessorInterface
{
    public const ORDER = 20;

    public const TAG = 'commands';

    public function getProcessorName(): string
    {
        return __('Commands');
    }

    public function getAuditSection(): string
    {
        return __('Dependency Injection (DI)');
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'noProxyUsedInCommands' => $this->getNoProxyUsedEntry()
            ],
            'warnings' => []
        ];
    }

    private function getNoProxyUsedEntry(): array
    {
        $title = __('No Proxy used for the command');
        $explanation = __('Injections in commands must be done through a proxy. All injections are made every time a command is launched. This can lead to performance issues for command running, including crons. Proxies help to avoid this issue by injecting an instance of a pseudo-class.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
        ];
    }

    public function run(): void
    {

        $commandsListNode = $this->getContent()->xpath('//type[@name=\'Magento\Framework\Console\CommandList\']//item');

        foreach ($commandsListNode as $commandNode) {
            $this->manageCommandNode($commandNode, $this->getContent());
        }
    }

    private function manageCommandNode($commandNode, $input): void
    {
        $commandClassName = (string) $commandNode;
        $proxies = $this->getCommandProxies($input, $commandClassName);
        $commandClass = new \ReflectionClass($commandClassName);
        $constructor = $commandClass->getConstructor();
        $constructorParameters = [];
        if ($constructor) {
            $constructorParameters = $constructor->getParameters();
        }

        if (empty($proxies) || count($proxies) < count($constructorParameters) - 1) {
            foreach ($constructorParameters as $constructorParameter) {
                $parameterClass = $constructorParameter->getType();
                if ($parameterClass) {
                    $parameterClassName = $parameterClass->getName();
                    if (!str_contains((string) $parameterClassName, 'Factory') && !in_array($parameterClassName, $proxies)) {
                        $this->results['errors']['noProxyUsedInCommands']['files'][] = $commandClassName;
                        $this->results['hasErrors'] = true;
                        $this->addErroneousFile($commandClassName, Audit::PRIORITY_AVERAGE);
                        break;
                    }
                }
            }
        }
    }

    private function getCommandProxies($input, $commandClass)
    {
        $commandsListNode = $input->xpath('//type[@name=\'' . $commandClass . '\']//argument');

        $proxies = [];
        foreach ($commandsListNode as $commandsNode) {
            $argumentClassName = (string) $commandsNode;
            if (strpos($argumentClassName, 'Proxy') !== false) {
                $proxies[] = $argumentClassName;
            }
        }
        return $proxies;
    }
}
