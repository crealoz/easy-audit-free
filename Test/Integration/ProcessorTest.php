<?php

namespace Crealoz\EasyAudit\Test\Integration;

use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Magento\Framework\Filesystem\DriverInterface;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    protected array $processors = [];

    protected function setUp(): void
    {
        $baseDir = __DIR__ . '/../../Processor/Files/';
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        foreach ($files as $file) {
            require_once $file;
            $className = $this->getClassNameFromFile($file);
            if (class_exists($className)) {
                /** Check that class in not abstract or an interface */
                $reflection = new \ReflectionClass($className);
                if (!$reflection->isAbstract() && !$reflection->isInterface() && $reflection->implementsInterface(AuditProcessorInterface::class)) {
                    $constructor = $reflection->getConstructor();
                    $parameters = $constructor ? $constructor->getParameters() : [];

                    $dependencies = [];
                    foreach ($parameters as $parameter) {
                        if ($parameter->getName() === 'auditStorage') {
                            $dependencies[] = $this->getMockAuditStorage();
                        } else {
                            $dependencies[] = $this->getMockDependency($parameter->getType());
                        }
                    }

                    $processor = $reflection->newInstanceArgs($dependencies);

                    // Appeler setFile sur la classe parent
                    if (method_exists($processor, 'setFile')) {
                        $processor->setFile('fake_file_path'); // Faux fichier
                    }

                    if (method_exists($processor, 'setArray')) {
                        $processor->setArray([]);
                    }

                    if (method_exists($processor, 'getContent')) {
                        $file = $this->createTempXmlFile();
                        $processor->setFile($file);
                    }

                    $this->processors[$className] = [
                        'object' =>$processor,
                        'reflection' => $reflection
                    ];
                }
            }
        }
    }

    protected function tearDown(): void
    {
        unset($this->processors);
    }

    private function getClassNameFromFile($file)
    {
        $contents = file_get_contents($file);
        if (preg_match('/namespace\s+([^;]+);/i', $contents, $namespaceMatch) &&
            preg_match('/class\s+([^\s{]+)/i', $contents, $classMatch)) {
            return trim($namespaceMatch[1]) . '\\' . trim($classMatch[1]);
        }
        return null;
    }

    private function getMockAuditStorage()
    {
        $mock = $this->createMock(\Crealoz\EasyAudit\Model\AuditStorage::class);
        $mock->method('getIgnoredModules')->willReturn(['Module1', 'Module2']);
        return $mock;
    }

    private function getMockDependency($class)
    {
        if ($class === null) {
            return null;
        }

        if (ltrim(DriverInterface::class, '\\') === ltrim((string) $class, '\\')) {
            $mock = $this->createMock(DriverInterface::class);
            $mock->method('fileGetContents')->willReturn('mocked file content');
            return $mock;
        }

        return $this->createMock($class->getName());
    }

    private function createTempXmlFile(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_xml_');
        file_put_contents($tempFile, '<?xml version="1.0"?><root><child>value</child></root>');
        return $tempFile;
    }

    public function testProcessors()
    {
        foreach ($this->processors as $class => $processor) {
            $this->testProcessor($class, $processor['object']);
            $this->testReflection($processor['reflection'], $processor['object']);
        }
    }

    private function testProcessor($class, $processor)
    {
        $this->assertInstanceOf(AuditProcessorInterface::class, $processor);

        $processor->prepopulateResults();
        $this->assertFalse($processor->hasErrors(), $class . ' should not have errors after prepopulateResults');

        $processor->run();
        $results = $processor->getResults();
        $this->assertNotEmpty($results, $class . ' should produce non-empty results after run');

        $processorName = $processor->getProcessorName();
        $this->assertIsString($processorName, $class . ' should return a string as processor name');

        $processorTag = $processor->getProcessorTag();
        $this->assertIsString($processorTag, $class . ' should return a string as processor tag');
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $processorTag, $class . ' should return a tag without spaces or special characters');

        $auditSection = $processor->getAuditSection();
        $this->assertIsString($auditSection, $class . ' should return a string as audit section');

        $erroneousFiles = $processor->getErroneousFiles();
        $this->assertIsArray($erroneousFiles, $class . ' should return an array as erroneous files');

        $hasErrors = $processor->hasErrors();
        $this->assertIsBool($hasErrors, $class . ' should return a boolean from hasErrors');
    }

    private function testReflection(\ReflectionClass $class, AuditProcessorInterface $processor)
    {
        $resultsProperty = $class->getProperty('results');
        $resultsProperty->setAccessible(true);

        $resultsProperty->setValue($processor, [
            'hasErrors' => true,
            'errors' => ['error'],
            'warnings' => [],
            'suggestions' => [],
        ]);

        $hasErrors = $processor->hasErrors();
        $this->assertTrue($hasErrors, $class . ' should return true from hasErrors when errors are present');

        $resultsProperty->setValue($processor, []);
        $this->assertFalse($processor->hasErrors(), $class . ' should return false when no errors are present');

        $erroneousFilesProperty = $class->getProperty('erroneousFiles');
        $erroneousFilesProperty->setAccessible(true);

        $addErroneousFileMethod = $class->getMethod('addErroneousFile');
        $addErroneousFileMethod->setAccessible(true);

        $addErroneousFileMethod->invoke($processor, 'file1', 5);
        $erroneousFiles = $erroneousFilesProperty->getValue($processor);
        $this->assertEquals(['file1' => 5], $erroneousFiles, $class . ' should add a new erroneous file with the correct score');

        $addErroneousFileMethod->invoke($processor, 'file1', 3);
        $erroneousFiles = $erroneousFilesProperty->getValue($processor);
        $this->assertEquals(['file1' => 8], $erroneousFiles, $class . ' should increment the score of an existing erroneous file');

        $getIgnoredModulesMethod = $class->getMethod('getIgnoredModules');
        $getIgnoredModulesMethod->setAccessible(true);
        $ignoredModules = $getIgnoredModulesMethod->invoke($processor);
        $this->assertEquals(['Module1', 'Module2'], $ignoredModules, $class . ' should return the ignored modules');


    }

}