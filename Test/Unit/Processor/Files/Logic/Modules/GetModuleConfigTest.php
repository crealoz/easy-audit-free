<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors\Files\Logic\Modules;

use Crealoz\EasyAudit\Processor\Files\Logic\Modules\GetModuleConfig;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\Exception\FileSystemException;
use PHPUnit\Framework\TestCase;

class GetModuleConfigTest extends TestCase
{
    private $moduleTools;
    private $getModuleConfig;

    protected function setUp(): void
    {
        $this->moduleTools = $this->createMock(ModuleTools::class);
        $this->getModuleConfig = new GetModuleConfig($this->moduleTools);
    }

    public function testProcess()
    {
        $input = ['vendor/provider/module/etc/module.xml', 'app/code/Vendor/Module/etc/module.xml'];
        $disabledModules = ['Vendor_Module1', 'Vendor_Module2'];
        $enabledModules = ['Vendor_Module3'];

        $this->moduleTools->method('getAllModules')->willReturn(array_merge($disabledModules, $enabledModules));
        $this->moduleTools->method('getEnabledModules')->willReturn($enabledModules);
        $this->moduleTools->method('getModuleNameByModuleXml')
            ->willReturnOnConsecutiveCalls('Vendor_Module1', 'Vendor_Module2');

        $result = $this->getModuleConfig->process($input);

        $this->assertEquals($disabledModules, $result);
    }

    public function testProcessWithException()
    {
        $this->moduleTools->method('getModuleNameByModuleXml')
            ->willThrowException(new FileSystemException(__('File not found')));

        $this->expectException(FileSystemException::class);

        $this->getModuleConfig->process(['invalid/path/module.xml']);
    }
}