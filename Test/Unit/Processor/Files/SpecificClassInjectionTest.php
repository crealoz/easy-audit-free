<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors\Files;

use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\Code\SpecificClassInjection;
use Crealoz\EasyAudit\Service\Classes\ArgumentTypeChecker;
use Crealoz\EasyAudit\Service\Classes\ConstructorService;
use Crealoz\EasyAudit\Service\Classes\HasModelAnInterface;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Magento\Framework\ObjectManager\DefinitionInterface;
use PHPUnit\Framework\TestCase;

class SpecificClassInjectionTest extends TestCase
{
    private $specificClassInjection;
    private $auditStorage;
    private $classNameGetter;
    private $definitions;
    private $hasModelAnInterface;
    private $argumentTypeChecker;
    private $constructorService;

    protected function setUp(): void
    {
        $this->auditStorage = $this->createMock(AuditStorage::class);
        $this->classNameGetter = $this->createMock(ClassNameGetter::class);
        $this->definitions = $this->createMock(DefinitionInterface::class);
        $this->hasModelAnInterface = $this->createMock(HasModelAnInterface::class);
        $this->argumentTypeChecker = $this->createMock(ArgumentTypeChecker::class);
        $this->constructorService = $this->createMock(ConstructorService::class);

        $this->specificClassInjection = new SpecificClassInjection(
            $this->auditStorage,
            $this->classNameGetter,
            $this->definitions,
            $this->hasModelAnInterface,
            $this->argumentTypeChecker,
            $this->constructorService
        );
    }

    protected function tearDown(): void
    {
        unset($this->specificClassInjection);
        unset($this->auditStorage);
        unset($this->classNameGetter);
        unset($this->definitions);
        unset($this->hasModelAnInterface);
        unset($this->argumentTypeChecker);
        unset($this->constructorService);
    }

    public function testGetProcessorName()
    {
        $this->assertEquals('Specific Class Injection', $this->specificClassInjection->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('PHP', $this->specificClassInjection->getAuditSection());
    }

    public function testPrepopulateResults()
    {
        $this->specificClassInjection->prepopulateResults();
        $results = $this->specificClassInjection->getResults();

        $this->assertArrayHasKey('errors', $results);
        $this->assertArrayHasKey('warnings', $results);
        $this->assertArrayHasKey('suggestions', $results);
    }

    public function testRun()
    {
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn('SomeClass');
        $this->definitions->method('getParameters')->willReturn([
            ['param1', 'SomeClass'],
            ['param2', 'AnotherClass']
        ]);
        $this->constructorService->method('isConstructorOverridden')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentModel')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentCollection')->willReturn(false);
        $this->argumentTypeChecker->method('isArgumentRepository')->willReturn(false);
        $this->hasModelAnInterface->method('execute')->willReturn(false);
        $this->argumentTypeChecker->method('isArgumentResourceModel')->willReturn(false);

        $this->specificClassInjection->setFile('/path/to/file.php');
        $this->specificClassInjection->run();
        $results = $this->specificClassInjection->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('specificClassInjection', $results['warnings']);
    }

    public function testIsArgumentRepository()
    {
        $className = 'SomeClass';
        $argumentName = 'SomeRepository';
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn($className);
        $this->definitions->method('getParameters')->willReturn([
            ['param1', $argumentName]
        ]);
        $this->constructorService->method('isConstructorOverridden')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentModel')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentRepository')->willReturn(true);

        $this->specificClassInjection->setFile('/path/to/file.php');
        $this->specificClassInjection->run();
        $results = $this->specificClassInjection->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('repositoryMustUseInterface', $results['errors']);
        $this->assertArrayHasKey($className, $results['errors']['repositoryMustUseInterface']['files']);
        $this->assertContains($argumentName, $results['errors']['repositoryMustUseInterface']['files'][$className]);
    }

    public function testIsArgumentCollection()
    {
        $className = 'SomeClass';
        $argumentName = 'SomeCollection';
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn($className);
        $this->definitions->method('getParameters')->willReturn([
            ['param1', $argumentName]
        ]);
        $this->constructorService->method('isConstructorOverridden')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentModel')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentCollection')->willReturn(true);

        $this->specificClassInjection->setFile('/path/to/file.php');
        $this->specificClassInjection->run();
        $results = $this->specificClassInjection->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('collectionMustUseFactory', $results['errors']);
        $this->assertArrayHasKey($className, $results['errors']['collectionMustUseFactory']['files']);
        $this->assertContains($argumentName, $results['errors']['collectionMustUseFactory']['files'][$className]);
    }

    public function testSpecificModelInjection()
    {
        $className = 'SomeClass';
        $argumentName = 'SomeModel';
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn($className);
        $this->definitions->method('getParameters')->willReturn([
            ['param1', $argumentName]
        ]);
        $this->constructorService->method('isConstructorOverridden')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentModel')->willReturn(true);
        $this->hasModelAnInterface->method('execute')->willReturn(true);

        $this->specificClassInjection->setFile('/path/to/file.php');
        $this->specificClassInjection->run();
        $results = $this->specificClassInjection->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('specificModelInjection', $results['errors']);
        $this->assertArrayHasKey($className, $results['errors']['specificModelInjection']['files']);
        $this->assertContains($argumentName, $results['errors']['specificModelInjection']['files'][$className]);
    }

    public function testResourceModelInjection()
    {
        $className = 'SomeClass';
        $argumentName = 'SomeResourceModel';
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn($className);
        $this->definitions->method('getParameters')->willReturn([
            ['param1', $argumentName]
        ]);
        $this->constructorService->method('isConstructorOverridden')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentModel')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentResourceModel')->willReturn(true);
        $this->argumentTypeChecker->method('isArgumentRepository')->willReturn(false);

        $this->specificClassInjection->setFile('/path/to/file.php');
        $this->specificClassInjection->run();
        $results = $this->specificClassInjection->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('resourceModelInjection', $results['errors']);
        $this->assertArrayHasKey($className, $results['errors']['resourceModelInjection']['files']);
        $this->assertContains($argumentName, $results['errors']['resourceModelInjection']['files'][$className]);
    }
}