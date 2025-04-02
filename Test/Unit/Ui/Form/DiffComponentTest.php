<?php

namespace Crealoz\EasyAudit\Test\Unit\Ui\Component\Form;

use Crealoz\EasyAudit\Api\EntryRepositoryInterface;
use Crealoz\EasyAudit\Api\SubEntryRepositoryInterface;
use Crealoz\EasyAudit\Service\PrManager;
use Crealoz\EasyAudit\Ui\Component\Form\Field\Diff;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DiffTest extends TestCase
{
    private Diff $diffComponent;
    private MockObject $contextMock;
    private MockObject $uiComponentFactoryMock;
    private MockObject $entryRepositoryMock;
    private MockObject $subEntryRepositoryMock;
    private MockObject $prManagerMock;
    private MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->entryRepositoryMock = $this->createMock(EntryRepositoryInterface::class);
        $this->subEntryRepositoryMock = $this->createMock(SubEntryRepositoryInterface::class);
        $this->prManagerMock = $this->createMock(PrManager::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->diffComponent = new Diff(
            $this->contextMock,
            $this->uiComponentFactoryMock,
            $this->entryRepositoryMock,
            $this->subEntryRepositoryMock,
            $this->prManagerMock,
            $this->loggerMock
        );
    }

    public function testPrepareDataSourceWithValidDiff()
    {
        $queueId = 123;
        $expectedDiff = 'Some diff content';
        $dataSource = [
            'data' => [
                'general' => [
                    'diff' => null,
                    'queue_id' => $queueId
                ]
            ]
        ];

        $this->prManagerMock
            ->expects($this->once())
            ->method('getRemoteDiff')
            ->with($queueId)
            ->willReturn($expectedDiff);

        $result = $this->diffComponent->prepareDataSource($dataSource);

        $this->assertEquals($expectedDiff, $result['data']['general']['diff']);
    }

    public function testPrepareDataSourceWithNoSuchEntityException()
    {
        $queueId = 123;
        $dataSource = [
            'data' => [
                'general' => [
                    'diff' => null,
                    'queue_id' => $queueId
                ]
            ]
        ];

        $this->prManagerMock
            ->expects($this->once())
            ->method('getRemoteDiff')
            ->with($queueId)
            ->willThrowException(new NoSuchEntityException());

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with('A diff ID was requested but there are no results for this queue ID.');

        $result = $this->diffComponent->prepareDataSource($dataSource);

        $this->assertNull($result['data']['general']['diff'] ?? null);
    }

    public function testPrepareDataSourceWithLocalizedException()
    {
        $queueId = 123;
        $dataSource = [
            'data' => [
                'general' => [
                    'diff' => null,
                    'queue_id' => $queueId
                ]
            ]
        ];

        $this->prManagerMock
            ->expects($this->once())
            ->method('getRemoteDiff')
            ->with($queueId)
            ->willThrowException(new LocalizedException(__('Some localized error')));

        $this->loggerMock
            ->expects($this->never())
            ->method('error');

        $result = $this->diffComponent->prepareDataSource($dataSource);

        $this->assertNull($result['data']['general']['diff'] ?? null);
    }

    public function testPrepareDataSourceWithoutQueueId()
    {
        $dataSource = [
            'data' => [
                'general' => [
                    'diff' => null
                ]
            ]
        ];

        $this->prManagerMock
            ->expects($this->never())
            ->method('getRemoteDiff');

        $result = $this->diffComponent->prepareDataSource($dataSource);

        $this->assertNull($result['data']['general']['diff'] ?? null);
    }
}