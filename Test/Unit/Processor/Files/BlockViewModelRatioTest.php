<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors\Files;

use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\Code\BlockViewModelRatio;
use PHPUnit\Framework\TestCase;

class BlockViewModelRatioTest extends TestCase
{
    private $blockViewModelRatio;
    private $auditStorage;

    protected function setUp(): void
    {
        $this->auditStorage = $this->createMock(AuditStorage::class);
        $this->blockViewModelRatio = new BlockViewModelRatio($this->auditStorage);
    }

    protected function tearDown(): void
    {
        unset($this->blockViewModelRatio);
        unset($this->auditStorage);
    }

    public function testGetProcessorName()
    {
        $this->assertEquals('Block vs ViewModel Ratio', $this->blockViewModelRatio->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('PHP', $this->blockViewModelRatio->getAuditSection());
    }

    public function testPrepopulateResults()
    {
        $this->blockViewModelRatio->prepopulateResults();
        $results = $this->blockViewModelRatio->getResults();
        $this->assertArrayHasKey('warnings', $results);
        $this->assertArrayHasKey('blockViewModelRatio', $results['warnings']);
    }

    public function testRun()
    {
        $files = [
            '/path/Vendor/Module/Block/File1.php',
            '/path/Vendor/Module/ViewModel/File2.php',
            '/path/Vendor/Module/Block/File3.php',
            '/path/Vendor/Module/Block/File4.php',
        ];

        $this->blockViewModelRatio->setArray($files);
        $this->auditStorage->method('isModuleIgnored')->willReturn(false);

        $this->blockViewModelRatio->run();
        $results = $this->blockViewModelRatio->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('blockViewModelRatio', $results['warnings']);
        $this->assertArrayHasKey('Vendor_Module', $results['warnings']['blockViewModelRatio']['files']);
        $this->assertEquals(0.75, $results['warnings']['blockViewModelRatio']['files']['Vendor_Module']);
    }

    public function testGetProcessorTag()
    {
        $this->assertEquals('BlockVsViewModelRatio', $this->blockViewModelRatio->getProcessorTag());
    }
}