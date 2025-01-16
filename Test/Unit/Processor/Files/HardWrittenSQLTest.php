<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors\Files;

use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\Code\HardWrittenSQL;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use PHPUnit\Framework\TestCase;

class HardWrittenSQLTest extends TestCase
{
    private $hardWrittenSQL;
    private $auditStorage;
    private $driver;

    protected function setUp(): void
    {
        $this->auditStorage = $this->createMock(AuditStorage::class);
        $this->driver = $this->createMock(DriverInterface::class);
        $this->driver->method('fileGetContents')->willReturnMap(
            [
                ['/path/to/empty.php', null, null, 'toto'],
                ['/path/to/error.php',  null, null, '
                    $select = "SELECT * FROM table";
                    $delete = "DELETE FROM table";
                    $update = "UPDATE table SET column = value";
                    $insert = "INSERT INTO table (column) VALUES (value)";
                    $join = "SELECT * FROM table1 JOIN table2 ON table1.id = table2.id";
                ']
            ]
        );
        $this->hardWrittenSQL = new HardWrittenSQL($this->auditStorage, $this->driver);
    }

    protected function tearDown(): void
    {
        unset($this->hardWrittenSQL);
        unset($this->auditStorage);
        unset($this->driver);
    }

    public function testGetProcessorName()
    {
        $this->assertEquals('Hard Written SQL', $this->hardWrittenSQL->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('PHP', $this->hardWrittenSQL->getAuditSection());
    }

    public function testPrepopulateResults()
    {
        $this->hardWrittenSQL->prepopulateResults();
        $results = $this->hardWrittenSQL->getResults();
        $this->assertArrayHasKey('errors', $results);
        $this->assertArrayHasKey('warnings', $results);
        $this->assertArrayHasKey('suggestions', $results);
    }

    /**
     * @throws FileSystemException
     */
    public function testRun()
    {

        $results = $this->getResultsFromFileContent('/path/to/empty.php');

        $this->assertFalse($results['hasErrors']);
        $this->testArrayKeysExist($results);

        $results = $this->getResultsFromFileContent('/path/to/error.php');

        $this->assertNotEmpty($results, 'Results should not be empty');
        $this->assertTrue($results['hasErrors'], 'Results should indicate errors');
        $this->testArrayKeysExist($results);

        $this->assertNotEmpty($results['errors']['hardWrittenSQLSelect']['files']);
        $this->assertNotEmpty($results['errors']['hardWrittenSQLDelete']['files']);
        $this->assertNotEmpty($results['warnings']['hardWrittenSQLUpdate']['files']);
        $this->assertNotEmpty($results['warnings']['hardWrittenSQLInsert']['files']);
        $this->assertNotEmpty($results['suggestions']['hardWrittenSQLJoin']['files']);

    }

    private function getResultsFromFileContent($path)
    {
        $content = $this->driver->fileGetContents($path);
        $this->assertNotNull($content, 'fileGetContents should not return null for ' . $path);

        $this->hardWrittenSQL->prepopulateResults();
        $this->hardWrittenSQL->setFile($path);
        $this->hardWrittenSQL->run();
        return $this->hardWrittenSQL->getResults();
    }

    private function testArrayKeysExist($results)
    {
        $this->assertArrayHasKey('hardWrittenSQLSelect', $results['errors']);
        $this->assertArrayHasKey('hardWrittenSQLDelete', $results['errors']);
        $this->assertArrayHasKey('hardWrittenSQLUpdate', $results['warnings']);
        $this->assertArrayHasKey('hardWrittenSQLInsert', $results['warnings']);
        $this->assertArrayHasKey('hardWrittenSQLJoin', $results['suggestions']);
    }

    public function testGetProcessorTag()
    {
        $this->assertEquals('hardWrittenSQL', $this->hardWrittenSQL->getProcessorTag());
    }
}