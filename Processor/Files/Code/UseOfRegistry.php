<?php

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Processor\Files\AbstractProcessor;
use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Crealoz\EasyAudit\Service\Parser\ConstructorArguments;
use Magento\Framework\Exception\FileSystemException;

class UseOfRegistry extends AbstractProcessor implements ProcessorInterface
{


    public function __construct(
        private readonly ClassNameGetter $classNameGetter,
        private readonly ConstructorArguments $constructorArgumentsGetter
    )
    {
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'useOfRegistry' => $this->getUseOfRegistryEntry(),
            ],
            'warnings' => [],
            'suggestions' => []
        ];
    }

    private function getUseOfRegistryEntry(): array
    {
        $title = __('Use of Registry');
        $explanation = __('The use of the Registry is deprecated. Use dependency injection or data persistors instead.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    public function getProcessorName(): string
    {
        return __('Use of Registry');
    }

    public function getAuditSection(): string
    {
        return __('PHP');
    }

    public function run($input)
    {
        try {
            $className = $this->classNameGetter->getClassFullNameFromFile($input);
        } catch (NotAClassException|FileSystemException $e) {
            return;
        }

        try {
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            return;
        }

        // We get the constructor arguments
        $arguments = $this->constructorArgumentsGetter->execute($reflection);
        if (empty($arguments)) {
            return;
        }
        $fileErrorLevel = 0;
        foreach ($arguments as $argument) {
            if ($argument === null) {
                continue;
            }
            $argumentName = $argument->getName();
            if ($argumentName === 'Magento\Framework\Registry') {
                $this->results['hasErrors'] = true;
                $this->results['errors']['useOfRegistry']['files'][] = $className;
            }
        }

    }
}