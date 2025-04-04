<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Model\EntryRepository;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\Collection;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\CollectionFactory;
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

class EntryRepositoryTest extends TestCase
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
        $this->entry = $this->createMock(\Crealoz\EasyAudit\Model\Result\Entry::class);
        $this->resource = $this->createMock(Entry::class);
        $this->entryFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\Result\EntryFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultFactory = $this->createMock(SearchResultFactory::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);

        $this->entryRepository = new EntryRepository(
            $this->resource,
            $this->entryFactory,
            $this->collectionFactory,
            $this->searchResultFactory,
            $this->collectionProcessor,
            $this->searchCriteriaBuilder
        );
    }

    public function testSave()
    {
        $this->resource->expects($this->once())->method('save')->with($this->entry);

        $this->assertSame($this->entry, $this->entryRepository->save($this->entry));
    }

    public function testSaveException()
    {
        $this->resource->method('save')->willThrowException(new \Exception('Error'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the Entry: Error');

        $this->entryRepository->save($this->entry);
    }

    public function testGetById()
    {
        $this->entryFactory->method('create')->willReturn($this->entry);
        $this->resource->method('load')->with($this->entry, 1);
        $this->entry->method('getId')->willReturn(1);

        $this->assertSame($this->entry, $this->entryRepository->getById(1));
    }

    public function testGetByIdException()
    {
        $this->entryFactory->method('create')->willReturn($this->entry);
        $this->resource->method('load')->with($this->entry, 1);
        $this->entry->method('getId')->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Entry with id "1" does not exist.');

        $this->entryRepository->getById(1);
    }

    public function testGetList()
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $collection = $this->createMock(Collection::class);
        
        $this->collectionFactory->method('create')->willReturn($collection);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);
        
        $searchResult = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $this->searchResultFactory->method('create')->willReturn($searchResult);
        
        $entry1 = $this->createMock(\Crealoz\EasyAudit\Model\Result\Entry::class);
        $entry2 = $this->createMock(\Crealoz\EasyAudit\Model\Result\Entry::class);
        $collection->method('getItems')->willReturn([$entry1, $entry2]);
        $collection->method('getSize')->willReturn(2);
        
        $searchResult->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $searchResult->expects($this->once())->method('setItems')->with([$entry1, $entry2]);
        $searchResult->expects($this->once())->method('setTotalCount')->with(2);
        
        $this->assertSame($searchResult, $this->entryRepository->getList($searchCriteria));
    }

    public function testDelete()
    {
        $this->resource->expects($this->once())->method('delete')->with($this->entry);

        $this->assertTrue($this->entryRepository->delete($this->entry));
    }

    public function testDeleteException()
    {
        $this->resource->method('delete')->willThrowException(new \Exception('Error'));

        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessage('Could not delete the Entry: Error');

        $this->entryRepository->delete($this->entry);
    }

    public function testGetEntriesByResultId()
    {
        // Arrange
        $resultId = 1;
        $collectionMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class);
        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);

        $this->searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteriaMock);
        $this->collectionFactory->method('create')->willReturn($collectionMock);
        $this->collectionProcessor->method('process')->with($searchCriteriaMock, $collectionMock);
        $this->searchResultFactory->method('create')->willReturn($searchResultsMock);
        $searchResultsMock->method('getItems')->willReturn([$this->entry]);

        // Act
        $result = $this->entryRepository->getEntriesByResultId($resultId);

        // Assert
        $this->assertCount(1, $result);
        $this->assertInstanceOf(EntryInterface::class, $result[0]);
    }

    public function testHasEntries()
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
        $searchResultsMock->method('getItems')->willReturn([$this->entry]);

        $result = $this->entryRepository->hasEntries($resultId);

        $this->assertEquals(1, $result);
    }
}