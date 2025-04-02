<?php

namespace Crealoz\EasyAudit\Test\Unit\Processor\Files\Code;

use Crealoz\EasyAudit\Exception\Processor\FolderOrFileNotFoundException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Service\Config\AvailableCodingStandards;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Processor\Files\Code\PhpCs;


class PhpCsTest extends TestCase
{
    const ROOT_PATH= '/var/www/magento';
    /**
     * @var PhpCs|\PHPUnit\Framework\MockObject\MockObject
     */
    private $phpCs;

    private $auditStorage;
    private $moduleTools;
    private $directoryList;

    protected function setUp(): void
    {
        $this->auditStorage = $this->createMock(AuditStorage::class);
        $this->moduleTools = $this->createMock(ModuleTools::class);
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->directoryList->method('getRoot')->willReturn(self::ROOT_PATH);
        $this->availableStandards = $this->createMock(AvailableCodingStandards::class);
        // Create a partial mock of PhpCs
        $this->phpCs = new PhpCs(
            $this->auditStorage,
            $this->moduleTools,
            $this->directoryList,
            $this->availableStandards
        );
    }

    public function testGetProcessorName()
    {
        $this->assertEquals('Check PHP Code Sniffer', $this->phpCs->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('PHP', $this->phpCs->getAuditSection());
    }

    public function testCodingStandardsNotInstalled(): void
    {

        $this->availableStandards->method('getCodingStandards')->willReturn('Generic, PSR1, PSR2, Squiz, Zend');

        $this->phpCs->prepopulateResults();
        $this->phpCs->run();
        $results = $this->phpCs->getresults();
        $this->assertArrayHasKey('files', $results['suggestions']['phpCsSuggestions']);
        $this->assertNotEmpty($results['suggestions']['phpCsSuggestions']['files']);
    }

    public function testCodingStandardsInstalled(): void
    {
        $this->availableStandards->method('getCodingStandards')->willReturn('PSR12, Magento2');
        $files = [
            'PayPal_Braintree',
            '/path/Vendor/Module/ViewModel/File2.php',
            '/path/Vendor/Module/Block/File3.php',
            '/path/Vendor/Module/Block/File4.php',
        ];

        $this->moduleTools->method('getModuleNameByAnyFile')->willReturn('Vendor_Module');

        $this->phpCs->setArray($files);
        $this->auditStorage->method('isModuleIgnored')->willReturn(false, true, false, false);
        $this->phpCs->prepopulateResults();
        try {
            $this->phpCs->run();
        } catch (\Exception $e) {
            // Should be an instance of FolderOrFileNotFoundException or \RuntimeException
            $this->assertTrue($e instanceof FolderOrFileNotFoundException || $e instanceof \RuntimeException);
        }
        $results = $this->phpCs->getresults();
        $this->assertArrayHasKey('files', $results['suggestions']['phpCsSuggestions']);
        $this->assertEmpty($results['suggestions']['phpCsSuggestions']['files']);
    }

}