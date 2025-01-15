<?php

namespace Crealoz\EasyAudit\Test\Unit\Processor\Type;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Processor\Type\AbstractType;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AbstractTypeTest extends TestCase
{
    private $fileGetterFactory;
    private $logger;
    private $abstractType;

    protected function setUp(): void
    {
        $this->fileGetterFactory = $this->createMock(FileGetterFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->abstractType = $this->getMockForAbstractClass(
            AbstractType::class,
            [$this->fileGetterFactory, $this->logger]
        );
    }

    public function testHasErrors()
    {
        $this->assertFalse($this->abstractType->hasErrors());
    }

    public function testProcess()
    {
        $subTypes = [
            'subType1' => [$this->createMock(AuditProcessorInterface::class)],
            'subType2' => [$this->createMock(AuditProcessorInterface::class)]
        ];
        $type = 'type';

        $fileGetter = $this->createMock(FileGetterInterface::class);
        $fileGetter->method('execute')->willReturn(['file1', 'file2']);

        $this->fileGetterFactory->method('create')->willReturn($fileGetter);

        $this->abstractType->expects($this->any())
            ->method('getProgressBarCount')
            ->willReturn(2);

        $this->abstractType->expects($this->any())
            ->method('doProcess')
            ->willReturn(true);

        $results = $this->abstractType->process($subTypes, $type);

        $this->assertIsArray($results);
    }

    public function testInitResults()
    {
        $processor = $this->createMock(AuditProcessorInterface::class);
        $processor->expects($this->once())->method('prepopulateResults');

        $subTypes = [
            'subType1' => [$processor]
        ];

        $this->abstractType->initResults($subTypes);
    }

    public function testGetErroneousFiles()
    {
        $this->assertIsArray($this->abstractType->getErroneousFiles());
    }
}