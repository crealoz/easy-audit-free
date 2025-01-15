<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors\Files;

use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToAfterPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToBeforePluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\ConfigProviderPluginException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins\AroundToAfter;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins\AroundToBefore;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins\CheckConfigProvider;
use Magento\Framework\App\Utility\Files;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PluginsTest extends TestCase
{
    private $plugins;
    private $auditStorage;
    private $aroundToAfter;
    private $aroundToBefore;
    private $checkConfigProvider;
    private $logger;
    private $filesUtility;

    protected function setUp(): void
    {
        $this->auditStorage = $this->createMock(AuditStorage::class);
        $this->aroundToAfter = $this->createMock(AroundToAfter::class);
        $this->aroundToBefore = $this->createMock(AroundToBefore::class);
        $this->checkConfigProvider = $this->createMock(CheckConfigProvider::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->filesUtility = $this->createMock(Files::class);

        $this->plugins = new Plugins(
            $this->auditStorage,
            $this->aroundToAfter,
            $this->aroundToBefore,
            $this->checkConfigProvider,
            $this->logger,
            $this->filesUtility
        );
    }

    protected function tearDown(): void
    {
        unset($this->plugins);
        unset($this->auditStorage);
        unset($this->aroundToAfter);
        unset($this->aroundToBefore);
        unset($this->checkConfigProvider);
        unset($this->logger);
        unset($this->filesUtility);
    }

    private function createDummyFile($content)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($filePath, $content);
        return $filePath;
    }

    public function testSameModulePlugin()
    {
        $dummyContent = <<<XML
<config>
    <type name="Vendor\Module\Class">
        <plugin name="pluginName" type="Vendor\Module\PluginClass"/>
    </type>
</config>
XML;
        $dummyFile = $this->createDummyFile($dummyContent);
        $this->plugins->setFile($dummyFile);

        $this->plugins->run();
        $results = $this->plugins->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('sameModulePlugin', $results['errors']);
        $this->assertContains('Vendor\Module\PluginClass', $results['errors']['sameModulePlugin']['files']);
    }

    public function testMagentoFrameworkPlugin()
    {
        $dummyContent = <<<XML
<config>
    <type name="Magento\Framework\Class">
        <plugin name="pluginName" type="Vendor\Module\PluginClass"/>
    </type>
</config>
XML;
        $dummyFile = $this->createDummyFile($dummyContent);
        $this->plugins->setFile($dummyFile);

        $this->plugins->run();
        $results = $this->plugins->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('magentoFrameworkPlugin', $results['errors']);
        $this->assertArrayHasKey('Magento\Framework\Class', $results['errors']['magentoFrameworkPlugin']['files']);
        $this->assertContains('Vendor\Module\PluginClass', $results['errors']['magentoFrameworkPlugin']['files']['Magento\Framework\Class']);
    }

    public function testNonExistentPluginFile()
    {
        $dummyContent = <<<XML
<config>
    <type name="Vendor\Module\Class">
        <plugin name="pluginName" type="Vendor\Module\NonExistentPluginClass"/>
    </type>
</config>
XML;
        $dummyFile = $this->createDummyFile($dummyContent);
        $this->plugins->setFile($dummyFile);

        $this->filesUtility->method('classFileExists')->willReturn(false);
        $this->plugins->run();
        $results = $this->plugins->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('nonExistentPluginFile', $results['warnings']);
        $this->assertContains('Vendor\Module\NonExistentPluginClass', $results['warnings']['nonExistentPluginFile']['files']);
    }

    public function testAroundToBeforePlugin()
    {
        $dummyContent = <<<XML
<config>
    <type name="Vendor\Module\Class">
        <plugin name="pluginName" type="Vendor\Module\PluginClass"/>
    </type>
</config>
XML;
        $dummyFile = $this->createDummyFile($dummyContent);
        $this->plugins->setFile($dummyFile);

        $this->aroundToBefore->method('execute')->willThrowException(new AroundToBeforePluginException(__('Error'), 'Vendor\Module\PluginClass'));
        $this->plugins->run();
        $results = $this->plugins->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('aroundToBeforePlugin', $results['warnings']);
        $this->assertContains('Vendor\Module\PluginClass', $results['warnings']['aroundToBeforePlugin']['files']);
    }

    public function testAroundToAfterPlugin()
    {
        $dummyContent = <<<XML
<config>
    <type name="Vendor\Module\Class">
        <plugin name="pluginName" type="Vendor\Module\PluginClass"/>
    </type>
</config>
XML;
        $dummyFile = $this->createDummyFile($dummyContent);
        $this->plugins->setFile($dummyFile);

        $this->aroundToAfter->method('execute')->willThrowException(new AroundToAfterPluginException(__('Error'), 'Vendor\Module\PluginClass'));
        $this->plugins->run();
        $results = $this->plugins->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('aroundToAfterPlugin', $results['warnings']);
        $this->assertContains('Vendor\Module\PluginClass', $results['warnings']['aroundToAfterPlugin']['files']);
    }

    public function testConfigProviderPlugin()
    {
        $dummyContent = <<<XML
<config>
    <type name="Vendor\Module\Class">
        <plugin name="pluginName" type="Vendor\Module\PluginClass"/>
    </type>
</config>
XML;
        $dummyFile = $this->createDummyFile($dummyContent);
        $this->plugins->setFile($dummyFile);

        $this->checkConfigProvider->method('execute')->willThrowException(new ConfigProviderPluginException(__('Error'), 'Vendor\Module\PluginClass'));
        $this->plugins->run();
        $results = $this->plugins->getResults();

        $this->assertTrue($results['hasErrors']);
        $this->assertArrayHasKey('configProviderPlugin', $results['errors']);
        $this->assertContains('Vendor\Module\PluginClass', $results['errors']['configProviderPlugin']['files']);
    }
}