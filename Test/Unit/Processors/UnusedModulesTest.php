<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors;

use Crealoz\EasyAudit\Exception\Processor\GeneralAuditException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\Logic\Modules\GetModuleConfig;
use Crealoz\EasyAudit\Processor\Files\Logic\UnusedModules;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Crealoz\EasyAudit\Processor\Files\Logic\UnusedModules
 */
class UnusedModulesTest extends TestCase
{
    private UnusedModules $processor;
    private GetModuleConfig $getModuleConfigMock;

    protected function setUp(): void
    {
        $auditStorage = $this->createMock(AuditStorage::class);
        $this->getModuleConfigMock = $this->createMock(GetModuleConfig::class);
        $this->processor = new UnusedModules($auditStorage, $this->getModuleConfigMock);
        $this->processor->prepopulateResults();
    }

    protected function tearDown(): void
    {
        unset($this->processor);
    }

    public function testRun(): void
    {
        $input = ['module1', 'module2', 'Magento_Module'];
        $this->getModuleConfigMock->method('process')->willReturn($input);

        $this->processor->setArray($input);

        $this->processor->run();

        $result = $this->processor->getResults();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('hasErrors', $result);
        $this->assertIsBool($result['hasErrors']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('unusedModules', $result['suggestions']);
        $this->assertIsArray($result['suggestions']['unusedModules']['files']);
    }

    public function testGetProcessorName(): void
    {
        $processorName = $this->processor->getProcessorName();
        $this->assertIsString($processorName);
    }

    public function testGetAuditSection(): void
    {
        $auditSection = $this->processor->getAuditSection();
        $this->assertIsString($auditSection);
    }

    public function testGetResults(): void
    {
        try {
            $results = $this->processor->getResults();
        } catch (GeneralAuditException $e) {
            $this->fail($e->getMessage());
        }
        $this->assertIsArray($results);
        $this->assertArrayHasKey('hasErrors', $results);
        $this->assertArrayHasKey('errors', $results);
        $this->assertArrayHasKey('warnings', $results);
        $this->assertArrayHasKey('suggestions', $results);
        $this->assertArrayHasKey('unusedModules', $results['suggestions']);
    }
}