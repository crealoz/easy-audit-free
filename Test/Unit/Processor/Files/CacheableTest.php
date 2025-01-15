<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors\Files;

use Crealoz\EasyAudit\Exception\Processor\GeneralAuditException;
use Crealoz\EasyAudit\Model\AuditStorage;
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
    private string $tempFile;

    public function setUp(): void
    {
        $auditStorage = $this->createMock(AuditStorage::class);
        $this->processor = new Cacheable($auditStorage);

        // Creates a temporary file with the XML string content
        $this->tempFile = tempnam(sys_get_temp_dir(), 'xml');
        file_put_contents($this->tempFile, $this->xmlString);
        $this->processor->prepopulateResults();
    }

    public function tearDown(): void
    {
        unlink($this->tempFile);
        unset($this->processor);
        unset($this->xmlString);
    }

    public function testRun(): void
    {
        $this->processor->setFile($this->tempFile);

        $this->processor->run();

        $result = $this->processor->getResults();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('hasErrors', $result);
        $this->assertIsBool($result['hasErrors']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('useCacheable', $result['suggestions']);
        $this->assertIsArray($result['suggestions']['useCacheable']);
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
        $this->assertArrayHasKey('useCacheable', $results['suggestions']);
    }
}

