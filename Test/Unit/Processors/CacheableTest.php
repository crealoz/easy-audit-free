<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors;

use Crealoz\EasyAudit\Exception\Processor\GeneralAuditException;
use Crealoz\EasyAudit\Processor\Files\View\Cacheable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Crealoz\EasyAudit\Processor\Files\View\Cacheable
 */
class CacheableTest extends TestCase
{
    private string $xmlString = '<?xml version="1.0"?>
        <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
            <body>
                <referenceContainer name="content">
                    <block class="Vendor\Module\Block\Example" name="example.block" template="Vendor_Module::example.phtml" cacheable="false"/>
                </referenceContainer>
                <referenceContainer name="sidebar.main">
                    <block class="Vendor\Module\Block\Sidebar" name="sidebar.block" template="Vendor_Module::sidebar.phtml"/>
                </referenceContainer>
            </body>
        </page>';

    private Cacheable $processor;

    public function setUp(): void
    {
        $this->processor = new Cacheable();

    }

    public function tearDown(): void
    {
        unset($this->processor);
    }

    public function testRun(): void
    {
        $xml = new \SimpleXMLElement($this->xmlString);
        $result = $this->processor->run($xml);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('hasErrors', $result);
        $this->assertIsBool($result['hasErrors']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('useCacheable', $result['warnings']);
        $this->assertIsArray($result['warnings']['useCacheable']);
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
        $this->assertArrayHasKey('useCacheable', $results['warnings']);
    }
}

