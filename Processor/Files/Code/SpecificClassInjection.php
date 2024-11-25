<?php

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractAuditProcessor;
use Crealoz\EasyAudit\Processor\Files\AbstractFileProcessor;
use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\Classes\ArgumentTypeChecker;
use Crealoz\EasyAudit\Service\Classes\ConstructorService;
use Crealoz\EasyAudit\Service\Classes\HasModelAnInterface;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Magento\Framework\Exception\FileSystemException;

/**
 * A class must not be injected in controller as a specific class. In all the cases, a factory or an interface should be used.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class SpecificClassInjection extends AbstractFileProcessor implements FileProcessorInterface
{
    private array $ignoredClass = [
        'Magento\Framework\Escaper',
        'Magento\Framework\Data\Collection\AbstractDb',
        'Magento\Framework\App\State',
        'Magento\Eav\Model\Validator\Attribute\Backend'
    ];

    public function __construct(
        AuditStorage $auditStorage,
        private readonly ClassNameGetter $classNameGetter,
        private readonly \Magento\Framework\ObjectManager\DefinitionInterface $definitions,
        private readonly HasModelAnInterface $hasModelAnInterface,
        private readonly ArgumentTypeChecker $argumentTypeChecker,
        private readonly ConstructorService $constructorService
    )
    {
        parent::__construct($auditStorage);
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
                'resourceModelInjection' => $this->getResourceModelInjectionEntry()
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

    private function getResourceModelInjectionEntry(): array
    {
        $title = __('Resource Model Injection');
        $explanation = __('A resource model must not be injected in constructor as a specific class. When a resource model is needed, a repository of it must be injected and used. It assures a better separation of concerns, a better code quality and improves the code maintainability.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
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

    /**
     * @return void
     * @todo ignore modules
     */
    public function run(): void
    {
        // First we get class name from the input that represents the file's path
        try {
            $className = $this->classNameGetter->getClassFullNameFromFile($this->getFile());
        } catch (NotAClassException|FileSystemException $e) {
            return;
        }

        try {
            if (!$this->constructorService->isConstructorOverridden($className)) {
                return;
            }
        } catch (\ReflectionException $e) {
            return;
        }

        $arguments = $this->definitions->getParameters($className);
        if (empty($arguments)) {
            return;
        }
        $fileErrorLevel = 0;
        foreach ($arguments as $argument) {
            if (!is_array($argument) || count($argument) < 2 || !is_string($argument[1])) {
                continue;
            }
            $argumentName = $argument[1];
            if (in_array($argumentName, $this->ignoredClass)) {
                continue;
            }
            if ($this->isArgumentIgnored($argumentName)) {
                continue;
            }
            if ($this->argumentTypeChecker->isArgumentModel($argumentName)) {
                if ($this->argumentTypeChecker->isArgumentCollection($argumentName)) {
                    $fileErrorLevel +=3;
                    $this->results['hasErrors'] = true;
                    $this->results['errors']['collectionMustUseFactory']['files'][$className][] = $argumentName;
                    continue;
                }
                if ($this->argumentTypeChecker->isArgumentRepository($argumentName)) {
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
                if ($this->argumentTypeChecker->isArgumentResourceModel($argumentName) && !$this->isClassRepository($argumentName)) {
                    $fileErrorLevel +=3;
                    $this->results['hasErrors'] = true;
                    $this->results['errors']['resourceModelInjection']['files'][$className][] = $argumentName;
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
        return $this->argumentTypeChecker->isArgumentBasicType($argumentName)
            || $this->argumentTypeChecker->isArgumentAnInterfaceOrFactory($argumentName)
            || $this->argumentTypeChecker->isArgumentMagentoModel($argumentName)
            || $this->argumentTypeChecker->isArgumentContext($argumentName)
            || $this->argumentTypeChecker->isArgumentRegistry($argumentName)
            || $this->argumentTypeChecker->isArgumentSession($argumentName)
            || $this->argumentTypeChecker->isArgumentHelper($argumentName)
            || $this->argumentTypeChecker->isArgumentStdLib($argumentName)
            || $this->argumentTypeChecker->isArgumentFileSystem($argumentName)
            || $this->argumentTypeChecker->isArgumentSerializer($argumentName)
            || $this->argumentTypeChecker->isArgumentGenerator($argumentName);
    }

    private function isClassRepository($argumentName): bool
    {
        return str_contains($argumentName, 'Repository');
    }

}