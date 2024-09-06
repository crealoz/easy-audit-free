<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors;

use Crealoz\EasyAudit\Exception\Processor\GeneralAuditException;
use Crealoz\EasyAudit\Service\Processor\Logic\UnusedModules;
use Crealoz\EasyAudit\Service\Processor\Logic\Modules\GetModuleConfig;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Crealoz\EasyAudit\Service\Processor\Logic\VendorUnusedModules
 */
class UnusedModulesTest extends TestCase
{
    private UnusedModules $processor;
    private GetModuleConfig $getModuleConfigMock;

    protected function setUp(): void
    {
        $this->getModuleConfigMock = $this->createMock(GetModuleConfig::class);
        $this->processor = new UnusedModules($this->getModuleConfigMock);
    }

    protected function tearDown(): void
    {
        unset($this->processor);
    }

    public function testRun(): void
    {
        $input = ['module1', 'module2', 'Magento_Module'];
        $this->getModuleConfigMock->method('process')->willReturn($input);

        $result = $this->processor->run($input);

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