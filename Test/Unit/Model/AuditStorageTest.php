<?php

namespace Crealoz\EasyAudit\Test\Unit\Model;

class AuditStorageTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->moduleReader = $this->getMockBuilder(\Magento\Framework\Module\Dir\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->auditStorage = new \Crealoz\EasyAudit\Model\AuditStorage($this->moduleReader);
    }

    public function testGetIgnoredModules()
    {
        $this->assertEquals([], $this->auditStorage->getIgnoredModules());
    }

    public function testSetIgnoredModules()
    {
        $this->moduleReader->expects($this->exactly(2))
            ->method('getModuleDir')
            ->willReturnCallback(
                function ($type, $module) {
                    return '/path/to/' . $module;
                }
            );

        $this->auditStorage->setIgnoredModules(['module1', 'module2']);

        $this->assertEquals(
            [
                'module1' => '/path/to/module1',
                'module2' => '/path/to/module2'
            ],
            $this->auditStorage->getIgnoredModules()
        );

        $this->assertTrue($this->auditStorage->isModuleIgnored('module1'));
        $this->assertTrue($this->auditStorage->isModuleIgnored('module2'));
        $this->assertFalse($this->auditStorage->isModuleIgnored('module3'));
    }
}