<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Model\Result;
use Crealoz\EasyAudit\Model\ResultRepository;
use Crealoz\EasyAudit\Model\ResourceModel\Result as ResultResource;
use Crealoz\EasyAudit\Api\Data\ResultInterface;
use Crealoz\EasyAudit\Model\ResultFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class ResultRepositoryTest extends TestCase
{
    private $resource;
    private $resultFactory;
    private $collectionFactory;
    private $searchResultFactory;
    private $collectionProcessor;
    private $searchCriteriaBuilder;
    private $resultRepository;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResultResource::class);
        $this->resultModel = $this->createMock(Result::class);
        $this->resultFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\ResultFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\ResourceModel\Result\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultFactory = $this->createMock(SearchResultFactory::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);

        $this->resultRepository = new ResultRepository(
            $this->resource,
            $this->resultFactory,
            $this->collectionFactory,
            $this->searchResultFactory,
            $this->collectionProcessor,
            $this->searchCriteriaBuilder
        );
    }

    public function testSave()
    {
        $this->resource->expects($this->once())->method('save')->with($this->resultModel);

        $this->assertSame($this->resultModel, $this->resultRepository->save($this->resultModel));
    }

    public function testSaveException()
    {
        $this->resource->method('save')->willThrowException(new \Exception('Error'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the Result: Error');

        $this->resultRepository->save($this->resultModel);
    }

    public function testGetById()
    {
        $this->resultFactory->method('create')->willReturn($this->resultModel);
        $this->resource->method('load')->with($this->resultModel, 1);
        $this->resultModel->method('getId')->willReturn(1);

        $this->assertSame($this->resultModel, $this->resultRepository->getById(1));
    }

    public function testGetByIdException()
    {
        $this->resultFactory->method('create')->willReturn($this->resultModel);
        $this->resource->method('load')->with($this->resultModel, 1);
        $this->resultModel->method('getId')->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Result with id "1" does not exist.');

        $this->resultRepository->getById(1);
    }

    public function testGetByQueueId()
    {
        $this->resultFactory->method('create')->willReturn($this->resultModel);
        $this->resource->method('load')->with($this->resultModel, 123, ResultInterface::QUEUE_ID);
        $this->resultModel->method('getId')->willReturn(123);

        $this->assertSame($this->resultModel, $this->resultRepository->getByQueueId(123));
    }

    public function testGetByQueueIdException()
    {
        $this->resultFactory->method('create')->willReturn($this->resultModel);
        $this->resource->method('load')->with($this->resultModel, 123, ResultInterface::QUEUE_ID);
        $this->resultModel->method('getId')->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Result with queue id "123" does not exist.');

        $this->resultRepository->getByQueueId(123);
    }

    public function testGetList()
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $collection = $this->createMock('\Crealoz\EasyAudit\Model\ResourceModel\Result\Collection');
        
        $this->collectionFactory->method('create')->willReturn($collection);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);
        
        $searchResult = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $this->searchResultFactory->method('create')->willReturn($searchResult);
        
        $result1 = $this->createMock(ResultInterface::class);
        $result2 = $this->createMock(ResultInterface::class);
        $collection->method('getItems')->willReturn([$result1, $result2]);
        $collection->method('getSize')->willReturn(2);
        
        $searchResult->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $searchResult->expects($this->once())->method('setItems')->with([$result1, $result2]);
        $searchResult->expects($this->once())->method('setTotalCount')->with(2);
        
        $this->assertSame($searchResult, $this->resultRepository->getList($searchCriteria));
    }

    public function testDelete()
    {
        $this->resource->expects($this->once())->method('delete')->with($this->resultModel);

        $this->assertTrue($this->resultRepository->delete($this->resultModel));
    }

    public function testDeleteException()
    {
        $this->resource->method('delete')->willThrowException(new \Exception('Error'));

        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessage('Could not delete the Result: Error');

        $this->resultRepository->delete($this->resultModel);
    }

    public function testHasResults()
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $collection = $this->createMock('\Crealoz\EasyAudit\Model\ResourceModel\Result\Collection');
        $this->collectionFactory->method('create')->willReturn($collection);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with(ResultInterface::REQUEST_ID, 456)
            ->willReturn($this->searchCriteriaBuilder);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);

        $searchResult = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $this->searchResultFactory->method('create')->willReturn($searchResult);
        $searchResult->method('getTotalCount')->willReturn(1);

        $this->assertTrue($this->resultRepository->hasResults(456));
    }

    public function testGetByRequestId()
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $collection = $this->createMock('\Crealoz\EasyAudit\Model\ResourceModel\Result\Collection');
        $this->collectionFactory->method('create')->willReturn($collection);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with(ResultInterface::REQUEST_ID, 789)
            ->willReturn($this->searchCriteriaBuilder);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);

        $searchResult = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $result1 = $this->createMock(ResultInterface::class);
        $result2 = $this->createMock(ResultInterface::class);
        $searchResult->method('getItems')->willReturn([$result1, $result2]);
        $this->searchResultFactory->method('create')->willReturn($searchResult);

        $results = $this->resultRepository->getByRequestId(789);
        $this->assertCount(2, $results);
        $this->assertSame([$result1, $result2], $results);
    }
}