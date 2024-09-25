<?php

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Processor\Files\AbstractProcessor;
use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;
use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\Classes\HasModelAnInterface;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
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
        'Magento\Framework\App\State'
    ];

    public function __construct(
        private readonly ClassNameGetter $classNameGetter,
        private readonly \Magento\Framework\ObjectManager\DefinitionInterface $definitions,
        private readonly HasModelAnInterface $hasModelAnInterface
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
                'specificModelInjection' => $this->getSpecificModelAsArgumentEntry(),
            ],
            'warnings' => [],
            'suggestions' => [
                'specificClassInjection' => $this->getSpecificClassAsArgumentEntry()
            ]
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
        $explanation = __('The classes below seem to implement an API interface. It is recommended to inject the interface instead of the class to prevent preferences to be ignored and respect the coding standards.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
        ];
    }

    private function getSpecificClassAsArgumentEntry(): array
    {
        $title = __('Specific Class Injection');
        $explanation = __('A class should not be injected in constructor as a specific class. In most of the cases, a factory, a builder or an interface should be used. This automatic scan can not be 100% accurate, please verify manually.');
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

        $arguments = $this->definitions->getParameters($className);
        if (empty($arguments)) {
            return;
        }
        $fileErrorLevel = 0;
        foreach ($arguments as $argument) {
            if ($argument === null || !is_array($argument) || count($argument) < 2 || !is_string($argument[1])) {
                continue;
            }
            $argumentName = $argument[1];
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
                if ($this->hasModelAnInterface->execute($argumentName)) {
                    $fileErrorLevel +=3;
                    $this->results['hasErrors'] = true;
                    $this->results['errors']['specificModelInjection']['files'][$className][] = $argumentName;
                    continue;
                }
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

    private function isArgumentModel(string $argumentName): bool
    {
        return str_contains($argumentName, 'Model');
    }

    /**
     * Check if type finishes by Factory or Interface
     *
     * @param $argument
     * @return bool
     */
    private function isArgumentAnInterfaceOrFactory(string $argumentName): bool
    {
        return str_ends_with($argumentName, 'Factory') || str_ends_with($argumentName, 'Interface');
    }

    private function isArgumentMagentoModel(string $argumentName): bool
    {
        return str_contains($argumentName, 'Magento\Framework\Model');
    }

    private function isArgumentBasicType(string $argumentName): bool
    {
        return in_array($argumentName, ['string', 'int', 'float', 'bool', 'array']);
    }

    private function isArgumentContext(string $argumentName): bool
    {
        return str_contains($argumentName, 'Context');
    }

    private function isArgumentStdLib(string $argumentName): bool
    {
        return str_contains($argumentName, 'StdLib');
    }

    private function isArgumentSerializer(string $argumentName): bool
    {
        return str_contains($argumentName, 'Serializer');
    }

    /**
     * Registry is managed elsewhere
     * @param $argumentName
     * @return bool
     */
    private function isArgumentRegistry(string $argumentName): bool
    {
        return $argumentName === 'Magento\Framework\Registry';
    }

    /**
     * Session is managed elsewhere it must use a proxy
     * @todo manage session
     * @param $argumentName
     * @return bool
     */
    private function isArgumentSession(string $argumentName): bool
    {
        return str_contains($argumentName, 'Session');
    }

    /**
     * Helper is managed elsewhere
     * @param $argumentName
     * @return bool
     */
    private function isArgumentHelper(string $argumentName): bool
    {
        return str_contains($argumentName, 'Helper');
    }

    /**
     * Filesystem is managed elsewhere
     * @todo: manage filesystem
     * @param $argumentName
     * @return bool
     */
    private function isArgumentFileSystem(string $argumentName): bool
    {
        return str_contains($argumentName, 'Magento\Framework\Filesystem');
    }

    private function isArgumentCollection(string $argumentName): bool
    {
        return str_contains($argumentName, 'Collection');
    }

    private function isArgumentRepository(string $argumentName): bool
    {
        return str_contains($argumentName, 'Repository');
    }
}