<?php

namespace Crealoz\EasyAudit\Test\Unit\Processor\Files\Code;

use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\Code\Helpers;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Crealoz\EasyAudit\Service\FileSystem\FileGetter;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Crealoz\EasyAudit\Service\ModuleTools;
use Crealoz\EasyAudit\Test\Mock\ExtendedHelper;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    private $auditStorage;
    private $fileGetterFactory;
    private $driver;
    private $io;
    private $classNameGetter;
    private $moduleTools;
    private $helpers;
    private string $extendHelperPath;
    private string $phtmlPath;

    protected function setUp(): void
    {
        $this->auditStorage = $this->createMock(AuditStorage::class);
        $this->fileGetterFactory = $this->createMock(FileGetterFactory::class);
        $this->driver = new \Magento\Framework\Filesystem\Driver\File();
        $this->io = $this->createMock(File::class);
        $this->classNameGetter = $this->createMock(ClassNameGetter::class);
        $this->moduleTools = $this->createMock(ModuleTools::class);
        $this->extendHelperPath = realpath(__DIR__ . '/../../../../Mock/ExtendedHelper.php');
        $this->phtmlPath = realpath(__DIR__ . '/../../../../Mock/view/phtml-with-helpers.phtml');

        $this->helpers = new Helpers(
            $this->auditStorage,
            $this->fileGetterFactory,
            $this->driver,
            $this->io,
            $this->classNameGetter,
            $this->moduleTools
        );
    }

    public function testGetProcessorName()
    {
        $this->assertEquals('Helpers', $this->helpers->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('PHP', $this->helpers->getAuditSection());
    }

    public function testPrepopulateResults()
    {
        $this->helpers->prepopulateResults();
        $results = $this->helpers->getResults();

        $this->assertArrayHasKey('hasErrors', $results);
        $this->assertArrayHasKey('errors', $results);
        $this->assertArrayHasKey('warnings', $results);
        $this->assertArrayHasKey('suggestions', $results);
    }

    public function testRun()
    {
        $this->auditStorage->method('isModuleIgnored')->willReturn(false);
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn(ExtendedHelper::class);

        $reflection = $this->createMock(\ReflectionClass::class);
        $reflection->method('isSubclassOf')->willReturn(true);

        $this->helpers->prepopulateResults();

        $this->helpers->setFile($this->extendHelperPath);
        $this->helpers->run();

        $results = $this->helpers->getResults();
        $this->assertTrue($results['hasErrors']);
    }

    public function testRetrieveHelpersInPhtml()
    {
        $phtmlFilesGetter = $this->createMock(FileGetter::class);
        $phtmlFilesGetter->method('execute')->willReturn([$this->phtmlPath]);
        $this->fileGetterFactory->method('create')->willReturn($phtmlFilesGetter);

        $this->moduleTools->method('getModuleNameByAnyFile')->willReturn('Crealoz_EasyAudit');
        $this->classNameGetter->method('getClassFullNameFromFile')->willReturn(ExtendedHelper::class);

        $this->helpers->prepopulateResults();

        $this->helpers->setFile($this->extendHelperPath);
        $this->helpers->run();

        $results = $this->helpers->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertEquals([
            ExtendedHelper::class => [
                'usageCount' => 2,
                $this->phtmlPath => 2
            ]
        ], $results['errors']['helpersInsteadOfViewModels']['files']);
    }
}