<?php

namespace Crealoz\EasyAudit\Test\Unit\Setup\Patch;

use Crealoz\EasyAudit\Setup\Patch\Data\ResultSeverityInitialization;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class SeverityTest extends \PHPUnit\Framework\TestCase
{
    private $moduleDataSetupMock;

    private $connectionMock;

    protected function setUp(): void
    {
        $this->moduleDataSetupMock = $this->getMockForAbstractClass(ModuleDataSetupInterface::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->moduleDataSetupMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
    }

    public function testApply()
    {
        // Expect startSetup and endSetup to be called
        $this->connectionMock
            ->expects($this->once())
            ->method('startSetup');

        $this->connectionMock
            ->expects($this->once())
            ->method('endSetup');

        // Expect insertArray with correct parameters
        $this->connectionMock
            ->expects($this->once())
            ->method('insertArray')
            ->with(
                $this->anything(), // table name
                ['level', 'color'], // columns
                [
                    ['level' => 'error', 'color' => 'FF0000'],
                    ['level' => 'warning', 'color' => 'FFA500'],
                    ['level' => 'suggestion', 'color' => 'FFFF00']
                ]
            );

        $patch = new ResultSeverityInitialization($this->moduleDataSetupMock);
        $patch->apply();
    }

    public function testRevert()
    {
        $this->connectionMock->expects($this->once())->method('startSetup');
        $this->connectionMock->expects($this->once())->method('delete');
        $this->connectionMock->expects($this->once())->method('endSetup');
        $patch = new ResultSeverityInitialization($this->moduleDataSetupMock);
        $patch->revert();
    }

    public function testAliases()
    {
        $patch = new ResultSeverityInitialization($this->moduleDataSetupMock);
        $this->assertEquals([], $patch->getAliases());
    }

    public function testDependencies()
    {
        $patch = new ResultSeverityInitialization($this->moduleDataSetupMock);
        $this->assertEquals([], $patch->getDependencies());
    }
}