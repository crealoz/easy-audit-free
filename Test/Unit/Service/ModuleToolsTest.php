<?php

namespace Crealoz\EasyAudit\Test\Unit\Service;

use Crealoz\EasyAudit\Service\ModuleTools;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use PHPUnit\Framework\TestCase;

class ModuleToolsTest extends TestCase
{
    private $driver;
    private $fullModuleList;
    private $moduleList;
    private $modulePaths;
    private $moduleTools;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->fullModuleList = $this->createMock(FullModuleList::class);
        $this->moduleList = $this->createMock(ModuleList::class);
        $this->modulePaths = $this->createMock(ModulePaths::class);

        $this->moduleTools = new ModuleTools(
            $this->driver,
            $this->fullModuleList,
            $this->moduleList,
            $this->modulePaths
        );
    }

    public function testGetModuleNameByModuleXmlFileNotFound()
    {
        $this->driver->method('isExists')->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found:');

        $this->moduleTools->getModuleNameByModuleXml('invalid/path/module.xml');
    }

    public function testGetModuleNameByModuleXmlInvalidXml()
    {
        $this->driver->method('isExists')->willReturn(true);
        $this->driver->method('fileGetContents')->willReturn('invalid xml content');

        $this->expectException(\Exception::class);

        $this->moduleTools->getModuleNameByModuleXml('valid/path/module.xml');
    }

    public function testGetModuleNameByModuleXmlValid()
    {
        $this->driver->method('isExists')->willReturn(true);
        $this->driver->method('fileGetContents')->willReturn('<config><module name="Vendor_Module"/></config>');

        $moduleName = $this->moduleTools->getModuleNameByModuleXml('valid/path/module.xml');

        $this->assertEquals('Vendor_Module', $moduleName);
    }

    public function testGetModuleNameByAnyFileEmptyFilePath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File path is empty');

        $this->moduleTools->getModuleNameByAnyFile('');
    }

    public function testGetModuleNameByAnyFileFileNotFound()
    {
        $this->modulePaths->method('getDeclarationXml')->willThrowException(new \InvalidArgumentException('File not found'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');

        $this->moduleTools->getModuleNameByAnyFile('invalid/path/file.php');
    }

    public function testGetAllModules()
    {
        $this->fullModuleList->method('getNames')->willReturn(['Vendor_Module1', 'Vendor_Module2']);

        $modules = $this->moduleTools->getAllModules();

        $this->assertEquals(['Vendor_Module1', 'Vendor_Module2'], $modules);
    }

    public function testGetEnabledModules()
    {
        $this->moduleList->method('getNames')->willReturn(['Vendor_Module1']);

        $modules = $this->moduleTools->getEnabledModules();

        $this->assertEquals(['Vendor_Module1'], $modules);
    }

    public function testGetModuleNameByAnyFileVendor()
    {
        $this->modulePaths->method('getDeclarationXml')->willReturn('vendor/provider/module/module.xml');
        $this->driver->method('isExists')->willReturn(true);
        $this->driver->method('fileGetContents')->willReturn('<config><module name="Vendor_Module"/></config>');

        $moduleName = $this->moduleTools->getModuleNameByAnyFile('vendor/provider/module/file.php');

        $this->assertEquals('Vendor_Module', $moduleName);
    }

    public function testGetModuleNameByAnyFileFalseVendor()
    {
        $this->modulePaths->method('getDeclarationXml')->willReturn('vendor/provider/module/module.xml');
        $this->driver->method('isExists')->willReturnOnConsecutiveCalls(false, true);
        $this->driver->method('fileGetContents')->willReturn('<config><module name="Vendor_Module"/></config>');

        $moduleName = $this->moduleTools->getModuleNameByAnyFile('app/code/provider/module/file.php', true);

        $this->assertEquals('Vendor_Module', $moduleName);
    }
}