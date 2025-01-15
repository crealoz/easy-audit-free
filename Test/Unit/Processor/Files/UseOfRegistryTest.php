<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors\Files;

use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\Code\UseOfRegistry;
use Crealoz\EasyAudit\Service\Classes\ConstructorService;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Magento\Framework\ObjectManager\DefinitionInterface;
use PHPUnit\Framework\TestCase;

class UseOfRegistryTest extends TestCase
{
    private $useOfRegistry;
    private $auditStorage;
    private $classNameGetter;
    private $definitions;
    private $constructorService;

    protected function setUp(): void
    {
        $this->auditStorage = $this->createMock(AuditStorage::class);
        $this->classNameGetter = $this->createMock(ClassNameGetter::class);
        $this->definitions = $this->createMock(DefinitionInterface::class);
        $this->constructorService = $this->createMock(ConstructorService::class);

        $this->useOfRegistry = new UseOfRegistry(
            $this->auditStorage,
            $this->classNameGetter,
            $this->definitions,
            $this->constructorService
        );
    }

    public function testGetProcessorName()
    {
        $this->assertEquals('Use of Registry', $this->useOfRegistry->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('PHP', $this->useOfRegistry->getAuditSection());
    }

    public function testPrepopulateResults()
    {
        $this->useOfRegistry->prepopulateResults();
        $results = $this->useOfRegistry->getResults();

        $this->assertArrayHasKey('errors', $results);
        $this->assertArrayHasKey('warnings', $results);
        $this->assertArrayHasKey('suggestions', $results);
        $this->assertArrayHasKey('useOfRegistry', $results['errors']);
    }

    public function testRunWithRegistry()
    {
        $className = 'SomeClass';
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn($className);
        $this->definitions->method('getParameters')->willReturn([
            ['param1', 'Magento\Framework\Registry']
        ]);
        $this->constructorService->method('isConstructorOverridden')->willReturn(true);

        $this->useOfRegistry->prepopulateResults();
        $this->useOfRegistry->setFile('/path/to/file.php');
        $this->useOfRegistry->run();
        $results = $this->useOfRegistry->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('useOfRegistry', $results['errors']);
        $this->assertContains($className, $results['errors']['useOfRegistry']['files']);
    }

    public function testRunWithoutRegistry()
    {
        $className = 'SomeClass';
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn($className);
        $this->definitions->method('getParameters')->willReturn([
            ['param1', 'SomeOtherClass']
        ]);
        $this->constructorService->method('isConstructorOverridden')->willReturn(true);

        $this->useOfRegistry->prepopulateResults();
        $this->useOfRegistry->setFile('/path/to/file.php');
        $this->useOfRegistry->run();
        $results = $this->useOfRegistry->getResults();

        $this->assertFalse($results['hasErrors']);
        $this->assertArrayHasKey('useOfRegistry', $results['errors']);
        $this->assertNotContains($className, $results['errors']['useOfRegistry']['files']);
    }

    protected function tearDown(): void
    {
        unset($this->useOfRegistry);
        unset($this->auditStorage);
        unset($this->classNameGetter);
        unset($this->definitions);
        unset($this->constructorService);
    }
}