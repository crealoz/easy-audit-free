<?php
namespace Crealoz\EasyAudit\Test\Unit\Ui\Component\Form\Field;

use Codeception\Test\Unit;
use Crealoz\EasyAudit\Api\EntryRepositoryInterface;
use Crealoz\EasyAudit\Api\SubEntryRepositoryInterface;
use Crealoz\EasyAudit\Ui\Component\Form\Field\Entries;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;

class EntriesTest extends Unit
{
    /** @var ContextInterface|MockObject */
    private $contextMock;

    /** @var UiComponentFactory|MockObject */
    private $uiComponentFactoryMock;

    /** @var EntryRepositoryInterface|MockObject */
    private $entryRepositoryMock;

    /** @var SubEntryRepositoryInterface|MockObject */
    private $subEntryRepositoryMock;

    /** @var Entries */
    private $entriesComponent;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->entryRepositoryMock = $this->createMock(EntryRepositoryInterface::class);
        $this->subEntryRepositoryMock = $this->createMock(SubEntryRepositoryInterface::class);

        $this->entriesComponent = new Entries(
            $this->contextMock,
            $this->uiComponentFactoryMock,
            $this->entryRepositoryMock,
            $this->subEntryRepositoryMock
        );
    }

    public function testPrepareDataSourceWithNoEntries()
    {
        $dataSource = [
            'data' => [
                'general' => [
                    'result_id' => 123
                ]
            ]
        ];

        $this->entryRepositoryMock
            ->expects($this->once())
            ->method('hasEntries')
            ->with(123)
            ->willReturn(false);

        $result = $this->entriesComponent->prepareDataSource($dataSource);

        $this->assertEquals($dataSource, $result);
        $this->assertArrayNotHasKey('entries', $result['data']['general']);
    }

    public function testPrepareDataSourceWithEntriesAndNoSubEntries()
    {
        $dataSource = [
            'data' => [
                'general' => [
                    'result_id' => 123
                ]
            ]
        ];

        $entryMock = $this->createMock(\Crealoz\EasyAudit\Api\Data\EntryInterface::class);
        $entryMock
            ->method('getEntry')
            ->willReturn('Test Entry');
        $entryMock->expects($this->once())
            ->method('getEntryId')
            ->willReturn(456);

        $this->entryRepositoryMock
            ->expects($this->once())
            ->method('hasEntries')
            ->with(123)
            ->willReturn(true);

        $this->entryRepositoryMock
            ->expects($this->once())
            ->method('getEntriesByResultId')
            ->with(123)
            ->willReturn([$entryMock]);

        $this->subEntryRepositoryMock
            ->expects($this->once())
            ->method('hasSubEntries')
            ->with(456)
            ->willReturn(false);

        $result = $this->entriesComponent->prepareDataSource($dataSource);

        $this->assertStringContainsString('Test Entry', $result['data']['general']['entries']);
    }

    public function testPrepareDataSourceWithEmptyEntry()
    {
        $dataSource = [
            'data' => [
                'general' => [
                    'result_id' => 123
                ]
            ]
        ];

        $entryMock = $this->createMock(\Crealoz\EasyAudit\Api\Data\EntryInterface::class);
        $entryMock
            ->method('getEntry')
            ->willReturn('');

        $this->entryRepositoryMock
            ->expects($this->once())
            ->method('hasEntries')
            ->with(123)
            ->willReturn(true);

        $this->entryRepositoryMock
            ->expects($this->once())
            ->method('getEntriesByResultId')
            ->with(123)
            ->willReturn([$entryMock]);

        $result = $this->entriesComponent->prepareDataSource($dataSource);

        $this->assertStringContainsString('', $result['data']['general']['entries']);
    }

    public function testPrepareDataSourceWithEntriesAndSubEntries()
    {
        $dataSource = [
            'data' => [
                'general' => [
                    'result_id' => 123
                ]
            ]
        ];

        $entryMock = $this->createMock(\Crealoz\EasyAudit\Api\Data\EntryInterface::class);
        $entryMock
            ->method('getEntry')
            ->willReturn('Main Entry');
        $entryMock->expects($this->exactly(2))
            ->method('getEntryId')
            ->willReturn(456);

        $subEntryMock = $this->createMock(\Crealoz\EasyAudit\Api\Data\SubEntryInterface::class);
        $subEntryMock
            ->method('getSubentry')
            ->willReturn('Sub Entry');

        $this->entryRepositoryMock
            ->expects($this->once())
            ->method('hasEntries')
            ->with(123)
            ->willReturn(true);

        $this->entryRepositoryMock
            ->expects($this->once())
            ->method('getEntriesByResultId')
            ->with(123)
            ->willReturn([$entryMock]);

        $this->subEntryRepositoryMock
            ->expects($this->once())
            ->method('hasSubEntries')
            ->with(456)
            ->willReturn(true);

        $this->subEntryRepositoryMock
            ->expects($this->once())
            ->method('getSubEntriesByEntryId')
            ->with(456)
            ->willReturn([$subEntryMock]);

        $result = $this->entriesComponent->prepareDataSource($dataSource);

        $this->assertStringContainsString('Main Entry', $result['data']['general']['entries']);
        $this->assertStringContainsString(' - Sub Entry', $result['data']['general']['entries']);
    }

    public function testPrepareDataSourceWithExceptionHandling()
    {
        $dataSource = [
            'data' => [
                'general' => [
                    'result_id' => 123
                ]
            ]
        ];

        $this->entryRepositoryMock
            ->expects($this->once())
            ->method('hasEntries')
            ->willThrowException(new \Exception('Test Exception'));

        $result = $this->entriesComponent->prepareDataSource($dataSource);

        $this->assertEquals($dataSource, $result);
    }
}