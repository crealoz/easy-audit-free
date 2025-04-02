<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Api\Data\SubEntryInterface;
use Crealoz\EasyAudit\Model\SubEntryRepository;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry\Collection;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry\CollectionFactory;
use Crealoz\EasyAudit\Api\Data\EntryInterface;
use Crealoz\EasyAudit\Model\Result\EntryFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class SubEntryRepositoryTest extends TestCase
{
    private $resource;
    private $entryFactory;
    private $collectionFactory;
    private $searchResultFactory;
    private $collectionProcessor;
    private $searchCriteriaBuilder;
    private $entryRepository;

    protected function setUp(): void
    {
        $this->subEntry = $this->createMock(\Crealoz\EasyAudit\Model\Result\Entry\SubEntry::class);
        $this->resource = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry::class);
        $this->subEntryFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntryFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultFactory = $this->createMock(SearchResultFactory::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);

        $this->subEntryRepository = new SubEntryRepository(
            $this->resource,
            $this->subEntryFactory,
            $this->collectionFactory,
            $this->searchResultFactory,
            $this->searchCriteriaBuilder,
            $this->collectionProcessor
        );
    }

    public function testSave()
    {
        $this->resource->expects($this->once())->method('save')->with($this->subEntry);

        $this->assertSame($this->subEntry, $this->subEntryRepository->save($this->subEntry));
    }

    public function testSaveException()
    {
        $this->resource->method('save')->willThrowException(new \Exception('Error'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the SubEntry: Error');

        $this->subEntryRepository->save($this->subEntry);
    }

    public function testGetById()
    {
        $this->subEntryFactory->method('create')->willReturn($this->subEntry);
        $this->resource->method('load')->with($this->subEntry, 1);
        $this->subEntry->method('getId')->willReturn(1);

        $this->assertSame($this->subEntry, $this->subEntryRepository->getById(1));
    }

    public function testGetByIdException()
    {
        $this->subEntryFactory->method('create')->willReturn($this->subEntry);
        $this->resource->method('load')->with($this->subEntry, 1);
        $this->subEntry->method('getId')->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('SubEntry with id "1" does not exist.');

        $this->subEntryRepository->getById(1);
    }

    public function testGetList()
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $collection = $this->createMock(Collection::class);
        
        $this->collectionFactory->method('create')->willReturn($collection);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);
        
        $searchResult = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $this->searchResultFactory->method('create')->willReturn($searchResult);
        
        $subEntry1 = $this->createMock(\Crealoz\EasyAudit\Model\Result\Entry\SubEntry::class);
        $subEntry2 = $this->createMock(\Crealoz\EasyAudit\Model\Result\Entry\SubEntry::class);
        $collection->method('getItems')->willReturn([$subEntry1, $subEntry2]);
        $collection->method('getSize')->willReturn(2);
        
        $searchResult->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $searchResult->expects($this->once())->method('setItems')->with([$subEntry1, $subEntry2]);
        $searchResult->expects($this->once())->method('setTotalCount')->with(2);
        
        $this->assertSame($searchResult, $this->subEntryRepository->getList($searchCriteria));
    }

    public function testDelete()
    {
        $this->resource->expects($this->once())->method('delete')->with($this->subEntry);

        $this->assertTrue($this->subEntryRepository->delete($this->subEntry));
    }

    public function testDeleteException()
    {
        $this->resource->method('delete')->willThrowException(new \Exception('Error'));

        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessage('Could not delete the SubEntry: Error');

        $this->subEntryRepository->delete($this->subEntry);
    }

    public function testGetSubEntriesByEntryId()
    {
        // Arrange
        $entryId = 1;
        $collectionMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class);
        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);

        $this->searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteriaMock);
        $this->collectionFactory->method('create')->willReturn($collectionMock);
        $this->collectionProcessor->method('process')->with($searchCriteriaMock, $collectionMock);
        $this->searchResultFactory->method('create')->willReturn($searchResultsMock);
        $searchResultsMock->method('getItems')->willReturn([$this->subEntry]);

        // Act
        $entries = $this->subEntryRepository->getSubEntriesByEntryId($entryId);

        // Assert
        $this->assertCount(1, $entries);
        $this->assertInstanceOf(SubEntryInterface::class, $entries[0]);
    }

    public function testHasSubEntries()
    {
        $resultId = 1;
        $collectionMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class);
        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $searchResultsMock->method('getTotalCount')->willReturn(1);

        $this->searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteriaMock);
        $this->collectionFactory->method('create')->willReturn($collectionMock);
        $this->collectionProcessor->method('process')->with($searchCriteriaMock, $collectionMock);
        $this->searchResultFactory->method('create')->willReturn($searchResultsMock);
        $searchResultsMock->method('getItems')->willReturn([$this->subEntry]);

        $result = $this->subEntryRepository->hasSubEntries($resultId);

        $this->assertEquals(1, $result);
    }
}