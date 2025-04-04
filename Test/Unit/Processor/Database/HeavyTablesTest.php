<?php
namespace Crealoz\EasyAudit\Test\Unit\Processor\Database;

use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Processor\Database\HeavyTables;
use Crealoz\EasyAudit\Model\AuditStorage;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class HeavyTablesTest extends TestCase
{
    private $auditStorageMock;
    private $resourceConnectionMock;
    private $connectionMock;
    private $heavyTables;

    protected function setUp(): void
    {
        $this->auditStorageMock = $this->createMock(AuditStorage::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->heavyTables = new HeavyTables(
            $this->auditStorageMock,
            $this->resourceConnectionMock
        );
    }

    public function testProcessorName()
    {
        $this->assertEquals('Heavy DB Tables', (string)$this->heavyTables->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('Database', (string)$this->heavyTables->getAuditSection());
    }

    public function testHeavyTablesWithLargeSize()
    {
        // Prepare table name mocking
        $this->connectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturnCallback(fn($tableName) => $tableName);

        // Configure table existence and row count
        $this->connectionMock->expects($this->any())
            ->method('isTableExists')
            ->willReturn(true);

        $this->connectionMock->expects($this->any())
            ->method('fetchOne')
            ->willReturnCallback(function($query) {
                $tableSizes = [
                    'SELECT COUNT(*) FROM dataflow_batch_export' => 15000,
                    'SELECT COUNT(*) FROM log_customer' => 20000,
                    'SELECT COUNT(*) FROM report_event' => 30000,
                ];
                return $tableSizes[$query] ?? 0;
            });


        // Prepare and run the processor
        $this->heavyTables->prepopulateResults();
        $this->heavyTables->run();

        // Get results and assert
        $results = $this->heavyTables->getResults();
        $this->assertTrue($results['hasError']);
        $this->assertCount(3, $results['warnings']['heavyTables']['tables']);
    }

    public function testNoHeavyTablesWhenBelowThreshold()
    {
        // Prepare table name mocking
        $this->connectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturnCallback(fn($tableName) => $tableName);

        // Configure table existence and row count
        $this->connectionMock->expects($this->any())
            ->method('isTableExists')
            ->willReturn(true);

        $this->connectionMock->expects($this->any())
            ->method('fetchOne')
            ->willReturn(5000);

        // Prepare and run the processor
        $this->heavyTables->prepopulateResults();
        $this->heavyTables->run();

        // Get results and assert
        $results = $this->heavyTables->getResults();
        $this->assertFalse($results['hasError']);
    }
}