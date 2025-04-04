<?php

namespace Crealoz\EasyAudit\Test\Unit\Processor\Files\Code;

use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\Code\PaymentInterfaceUseAudit;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\Filesystem\DriverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentInterfaceUseAuditTest extends TestCase
{
    /** @var AuditStorage|MockObject */
    private $auditStorageMock;

    /** @var DriverInterface|MockObject */
    private $driverMock;

    /** @var ModuleTools|MockObject */
    private $moduleToolsMock;

    /** @var PaymentInterfaceUseAudit */
    private $processor;

    protected function setUp(): void
    {
        $this->auditStorageMock = $this->createMock(AuditStorage::class);
        $this->driverMock = $this->createMock(DriverInterface::class);
        $this->moduleToolsMock = $this->createMock(ModuleTools::class);

        $this->processor = new PaymentInterfaceUseAudit(
            $this->auditStorageMock,
            $this->driverMock,
            $this->moduleToolsMock
        );
    }

    public function testGetProcessorName()
    {
        $this->assertEquals('Check the use of the PaymentInterface', $this->processor->getProcessorName());
    }

    public function testGetAuditSection()
    {
        $this->assertEquals('PHP', $this->processor->getAuditSection());
    }

    public function testPrepopulateResults()
    {
        $this->processor->prepopulateResults();
        $results = $this->processor->getResults();

        $this->assertArrayHasKey('hasErrors', $results);
        $this->assertArrayHasKey('errors', $results);
        $this->assertFalse($results['hasErrors']);
        $this->assertArrayHasKey('extensionOfAbstractMethod', $results['errors']);
    }

    public function testRunWhenModuleIsIgnored()
    {
        $filePath = '/path/to/file.php';
        $this->processor->setFile($filePath);

        $this->moduleToolsMock
            ->expects($this->once())
            ->method('getModuleNameByAnyFile')
            ->with($filePath)
            ->willReturn('TestModule');

        $this->auditStorageMock
            ->expects($this->once())
            ->method('isModuleIgnored')
            ->with('TestModule')
            ->willReturn(true);

        $this->driverMock
            ->expects($this->never())
            ->method('fileGetContents');

        $this->processor->run();
    }

    public function testRunWithNoAbstractMethodExtension()
    {
        $filePath = '/path/to/file.php';
        $fileContent = '<?php class TestPayment {}';

        $this->processor->setFile($filePath);

        $this->moduleToolsMock
            ->expects($this->once())
            ->method('getModuleNameByAnyFile')
            ->with($filePath)
            ->willReturn('TestModule');

        $this->auditStorageMock
            ->expects($this->once())
            ->method('isModuleIgnored')
            ->with('TestModule')
            ->willReturn(false);

        $this->driverMock
            ->expects($this->once())
            ->method('fileGetContents')
            ->with($filePath)
            ->willReturn($fileContent);

        $this->processor->run();
    }

    public function testRunWithAbstractMethodExtension()
    {
        $this->processor->prepopulateResults();
        $filePath = '/path/to/file.php';
        $fileContent = '<?php class TestPayment extends \Magento\Payment\Model\Method\AbstractMethod {}';

        $this->processor->setFile($filePath);

        $this->moduleToolsMock
            ->expects($this->once())
            ->method('getModuleNameByAnyFile')
            ->with($filePath)
            ->willReturn('TestModule');

        $this->auditStorageMock
            ->expects($this->once())
            ->method('isModuleIgnored')
            ->with('TestModule')
            ->willReturn(false);

        $this->driverMock
            ->expects($this->once())
            ->method('fileGetContents')
            ->with($filePath)
            ->willReturn($fileContent);

        $this->processor->run();

        // Use reflection to check the results
        $results = $this->processor->getResults();
        $this->assertArrayHasKey('files', $results['errors']['extensionOfAbstractMethod']);
        $this->assertContains($filePath, $results['errors']['extensionOfAbstractMethod']['files']);
    }
}