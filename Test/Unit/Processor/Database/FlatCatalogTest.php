<?php
namespace Crealoz\EasyAudit\Test\Unit\Processor\Database;

use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Processor\Database\FlatCatalog;
use Crealoz\EasyAudit\Model\AuditStorage;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;

class FlatCatalogTest extends TestCase
{
    private $auditStorageMock;
    private $storeManagerMock;
    private $scopeConfigMock;
    private $flatCatalog;

    protected function setUp(): void
    {
        $this->auditStorageMock = $this->createMock(AuditStorage::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->flatCatalog = new FlatCatalog(
            $this->auditStorageMock,
            $this->storeManagerMock,
            $this->scopeConfigMock
        );
    }

    public function testProcessorName()
    {
        $this->assertEquals('Check flat catalog for stores', (string)$this->flatCatalog->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('Database', (string)$this->flatCatalog->getAuditSection());
    }

    public function testFlatCatalogEnabledResultsInErrors()
    {
        // Prepare mock store
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $storeMock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('Default Store');

        // Configure store manager to return our mock store
        $this->storeManagerMock->expects($this->atLeastOnce())
            ->method('getStores')
            ->willReturn([$storeMock]);

        // Configure scope config to return flat catalog as enabled
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnCallback(function($path) {
                $paths = [
                    'catalog/frontend/flat_catalog_product' => true,
                    'catalog/frontend/flat_catalog_category' => true
                ];
                return $paths[$path] ?? false;
            });

        // Call run method
        $this->flatCatalog->prepopulateResults();
        $this->flatCatalog->run();

        // Get results and assert
        $results = $this->flatCatalog->getResults();
        $this->assertTrue($results['hasErrors']);
        $this->assertCount(1, $results['errors']['flatCatalog']['product']);
        $this->assertCount(1, $results['errors']['flatCatalog']['category']);
    }

    public function testNoErrorsWhenFlatCatalogDisabled()
    {
        // Prepare mock store
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        // Configure store manager to return our mock store
        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$storeMock]);

        // Configure scope config to return flat catalog as disabled
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnOnConsecutiveCalls(false, false);

        // Call run method
        $this->flatCatalog->prepopulateResults();
        $this->flatCatalog->run();

        // Get results and assert
        $results = $this->flatCatalog->getResults();
        $this->assertFalse($results['hasErrors']);
        $this->assertEmpty($this->flatCatalog->getErroneousFiles());
    }
}