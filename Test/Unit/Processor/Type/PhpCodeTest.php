<?php

namespace Crealoz\EasyAudit\Test\Unit\Processor\Type;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use Crealoz\EasyAudit\Api\Processor\Audit\ArrayProcessorInterface;
use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Model\AuditRequest;
use Crealoz\EasyAudit\Processor\Files\Code\BlockViewModelRatio;
use Crealoz\EasyAudit\Processor\Files\Code\UseOfRegistry;
use Crealoz\EasyAudit\Processor\Type\Logic;
use Crealoz\EasyAudit\Processor\Type\PHPCode;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Psr\Log\LoggerInterface;

class PhpCodeTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->fileGetterFactory = $this->createMock(FileGetterFactory::class);
        $fileGetter = $this->createMock(FileGetterInterface::class);
        $fileGetter->method('execute')->willReturn(['file1', 'file2', 'autoload.php', 'registration.php']);
        $this->fileGetterFactory->method('create')->willReturn($fileGetter);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->logic = new PHPCode($this->fileGetterFactory, $this->logger);

        $this->logicMock = $this->getMockBuilder(PHPCode::class)
            ->disableOriginalConstructor()
            ->getMock();

    }

    public function testProcess()
    {
        $arrayProcessor = $this->createMock(BlockViewModelRatio::class);
        $auditProcessor = $this->createMock(UseOfRegistry::class);
        $auditProcessor->expects($this->exactly(2))
            ->method('setFile')
            ->withAnyParameters();
        $auditProcessor->method('getFile')->willReturnOnConsecutiveCalls('file1', 'file2', 'autoload.php', 'registration.php');

        $subTypes = [
            'php' => [
                'array' => $arrayProcessor,
                'file' => $auditProcessor
            ]
        ];
        $type = 'php';

        $results = $this->logic->process($subTypes, $type);

        $this->assertIsArray($results);
    }

    public function testWrongProcessor()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Processor must implement AuditProcessorInterface');
        $subTypes = [
            'php' => [
                'array' => $this->createMock(AuditRequest::class)
            ]
        ];
        $type = 'php';

        $this->logic->process($subTypes, $type);
    }

    public function testProcessorHasError()
    {
        $arrayProcessor = $this->createMock(BlockViewModelRatio::class);
        $arrayProcessor->method('hasErrors')->willReturn(true);
        $auditProcessor = $this->createMock(UseOfRegistry::class);

        $subTypes = [
            'php' => [
                'array' => $arrayProcessor,
                'file' => $auditProcessor
            ]
        ];
        $type = 'php';

        $results = $this->logic->process($subTypes, $type);

        $this->assertIsArray($results);
    }

    public function testDoProcess()
    {

        // Method has protected visibility, so we need to use reflection to test it
        $method = new \ReflectionMethod(PHPCode::class, 'doProcess');
        $method->setAccessible(true);
        $auditProcessor = $this->createMock(AuditProcessorInterface::class);
        $auditProcessor->method('hasErrors')->willReturn(true);
        $this->assertTrue($method->invoke($this->logicMock, [
                'file' => $auditProcessor
        ], ['file1']));
    }
}