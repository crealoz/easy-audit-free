<?php

namespace Crealoz\EasyAudit\Test\Unit\Processor\Type;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use Crealoz\EasyAudit\Api\Processor\Audit\ArrayProcessorInterface;
use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Model\AuditRequest;
use Crealoz\EasyAudit\Processor\Files\Code\BlockViewModelRatio;
use Crealoz\EasyAudit\Processor\Files\Code\UseOfRegistry;
use Crealoz\EasyAudit\Processor\Type\Logic;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Psr\Log\LoggerInterface;

class LogicTest extends \PHPUnit\Framework\TestCase
{
    public $fileGetterFactory;
    /**
     * @var (\PHPUnit\Framework\MockObject\MockObject & \Psr\Log\LoggerInterface)
     */
    public $logger;
    public $logic;
    protected function setUp(): void
    {
        $this->fileGetterFactory = $this->createMock(FileGetterFactory::class);
        $fileGetter = $this->createMock(FileGetterInterface::class);
        $fileGetter->method('execute')->willReturn(['file1', 'file2']);
        $this->fileGetterFactory->method('create')->willReturn($fileGetter);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->logic = new Logic($this->fileGetterFactory, $this->logger);
    }

    public function testProcess()
    {
        $arrayProcessor = $this->createMock(BlockViewModelRatio::class);
        $arrayProcessor->expects($this->once())
            ->method('setArray')
            ->with(['file1', 'file2']);
        $arrayProcessor->method('getArray')->willReturn(['file1', 'file2']);
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
}