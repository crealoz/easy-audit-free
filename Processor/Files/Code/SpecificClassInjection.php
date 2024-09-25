<?php

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Processor\Files\AbstractProcessor;
use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;
use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Crealoz\EasyAudit\Service\Parser\ConstructorArguments;
use Magento\Framework\Exception\FileSystemException;

/**
 * A class must not be injected in controller as a specific class. In all the cases, a factory or an interface should be used.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class SpecificClassInjection extends AbstractProcessor implements ProcessorInterface
{
    private array $ignoredClass = [
        'Magento\Framework\Escaper',
        'Magento\Framework\Data\Collection\AbstractDb',
        'Magento\Framework\App\State',
        ''

    ];

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
                'collectionMustUseFactory' => $this->getCollectionMustUseFactoryEntry(),
                'repositoryMustUseInterface' => $this->getRepositoryMustUseInterfaceEntry(),
            ],
            'warnings' => [
                'specificModelInjection' => $this->getSpecificModelAsArgumentEntry(),
                'specificClassInjection' => $this->getSpecificClassAsArgumentEntry()
            ],
            'suggestions' => []
        ];
    }

    private function getCollectionMustUseFactoryEntry(): array
    {
        $title = __('Collection Must Use Factory');
        $explanation = __('A collection must not be injected in constructor as a specific class. When a collection is needed, a factory of it must be injected and used.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    private function getRepositoryMustUseInterfaceEntry(): array
    {
        $title = __('Repository Must Use Interface');
        $explanation = __('A repository must not be injected in constructor as a specific class. When a repository is needed, an interface of it must be injected and used.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    private function getSpecificModelAsArgumentEntry(): array
    {
        $title = __('Specific Model Injection');
        $explanation = __('A class, most of the time, must not be injected in constructor as a specific class. In all the cases, a factory or an interface should be used.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'manageSpecificClassInjection'
        ];
    }

    private function getSpecificClassAsArgumentEntry(): array
    {
        $title = __('Specific Class Injection');
        $explanation = __('A class should not be injected in constructor as a specific class. In all the cases, a factory, a builder or an interface should be used.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'manageSpecificClassInjection'
        ];
    }

    public function getProcessorName(): string
    {
        return __('Specific Class Injection');
    }

    public function getAuditSection(): string
    {
        return __('PHP');
    }

    public function run($input)
    {
        // First we get class name from the input that represents the file's path
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
            if (in_array($argumentName, $this->ignoredClass)) {
                continue;
            }
            if ($this->isArgumentIgnored($argumentName)) {
                continue;
            }
            if ($this->isArgumentModel($argumentName)) {
                if ($this->isArgumentCollection($argumentName)) {
                    $fileErrorLevel +=3;
                    $this->results['hasErrors'] = true;
                    $this->results['errors']['collectionMustUseFactory']['files'][$className][] = $argumentName;
                    continue;
                }
                if ($this->isArgumentRepository($argumentName)) {
                    $fileErrorLevel +=3;
                    $this->results['hasErrors'] = true;
                    $this->results['errors']['repositoryMustUseInterface']['files'][$className][] = $argumentName;
                    continue;
                }
                /** @todo check if the model of the argument implements an API interface */
            }
            $this->results['hasErrors'] = true;
            $this->results['warnings']['specificClassInjection']['files'][$className][] = $argumentName;
        }
        if ($fileErrorLevel > 10) {
            $this->addErroneousFile($input, Audit::PRIORITY_HIGH);
        } elseif ($fileErrorLevel > 5) {
            $this->addErroneousFile($input, Audit::PRIORITY_AVERAGE);
        } else {
            $this->addErroneousFile($input, Audit::PRIORITY_LOW);
        }
    }

    private function isArgumentIgnored($argumentName)
    {
        return $this->isArgumentBasicType($argumentName)
            || $this->isArgumentAnInterfaceOrFactory($argumentName)
            || $this->isArgumentMagentoModel($argumentName)
            || $this->isArgumentContext($argumentName)
            || $this->isArgumentRegistry($argumentName)
            || $this->isArgumentSession($argumentName)
            || $this->isArgumentHelper($argumentName)
            || $this->isArgumentStdLib($argumentName)
            || $this->isArgumentFileSystem($argumentName)
            || $this->isArgumentSerializer($argumentName);
    }

    private function isArgumentModel($argumentName)
    {
        return str_contains($argumentName, 'Model');
    }

    /**
     * Check if type finishes by Factory or Interface
     *
     * @param $argument
     * @return bool
     */
    private function isArgumentAnInterfaceOrFactory($argumentName)
    {
        return str_ends_with($argumentName, 'Factory') || str_ends_with($argumentName, 'Interface');
    }

    private function isArgumentMagentoModel($argumentName)
    {
        return str_contains($argumentName, 'Magento\Framework\Model');
    }

    private function isArgumentBasicType($argumentName)
    {
        return in_array($argumentName, ['string', 'int', 'float', 'bool', 'array']);
    }

    private function isArgumentContext($argumentName)
    {
        return str_contains($argumentName, 'Context');
    }

    private function isArgumentStdLib($argumentName)
    {
        return str_contains($argumentName, 'StdLib');
    }

    private function isArgumentSerializer($argumentName)
    {
        return str_contains($argumentName, 'Serializer');
    }

    /**
     * Registry is managed elsewhere
     * @param $argumentName
     * @return bool
     */
    private function isArgumentRegistry($argumentName)
    {
        return $argumentName === 'Magento\Framework\Registry';
    }

    /**
     * Session is managed elsewhere it must use a proxy
     * @todo manage session
     * @param $argumentName
     * @return bool
     */
    private function isArgumentSession($argumentName)
    {
        return str_contains($argumentName, 'Session');
    }

    /**
     * Helper is managed elsewhere
     * @param $argumentName
     * @return bool
     */
    private function isArgumentHelper($argumentName)
    {
        return str_contains($argumentName, 'Helper');
    }

    /**
     * Filesystem is managed elsewhere
     * @todo: manage filesystem
     * @param $argumentName
     * @return bool
     */
    private function isArgumentFileSystem($argumentName)
    {
        return str_contains($argumentName, 'Magento\Framework\Filesystem');
    }

    private function isArgumentCollection($argumentName)
    {
        return str_contains($argumentName, 'Collection');
    }

    private function isArgumentRepository($argumentName)
    {
        return str_contains($argumentName, 'Repository');
    }
}