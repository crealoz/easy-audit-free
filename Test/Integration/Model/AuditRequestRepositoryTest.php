<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest;

class AuditRequestRepositoryTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->resource = $this->createMock(AuditRequest::class);
        $auditRequest = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $this->requestFactory = $this->getMockBuilder(\Crealoz\EasyAudit\Model\AuditRequestFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->requestFactory->method('create')->willReturn($auditRequest);
        $this->searchResultFactory = $this->createMock(\Magento\Framework\Api\Search\SearchResultFactory::class);
        $this->collectionProcessor = $this->createMock(\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class);
        $this->collectionFactory = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\CollectionFactory::class);

        $this->auditRequestRepository = new \Crealoz\EasyAudit\Model\AuditRequestRepository(
            $this->resource,
            $this->requestFactory,
            $this->searchResultFactory,
            $this->collectionProcessor,
            $this->collectionFactory
        );
    }

    public function testSave()
    {
        $auditRequest = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $this->resource->expects($this->once())->method('save')->with($auditRequest);
        $this->auditRequestRepository->save($auditRequest);
    }

    public function testSaveException()
    {
        $auditRequest = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $this->resource->expects($this->once())->method('save')->with($auditRequest)->willThrowException(new \Exception('Error'));
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('Error');
        $this->auditRequestRepository->save($auditRequest);
    }

    public function testGetById()
    {
        $auditRequest = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $this->resource->expects($this->once())->method('load')->with($auditRequest, 1);
        $this->assertEquals($auditRequest, $this->auditRequestRepository->getById(1));
    }

    public function testDelete()
    {
        $auditRequest = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $this->resource->expects($this->once())->method('delete')->with($auditRequest);
        $this->auditRequestRepository->delete($auditRequest);
    }

    public function testDeleteById()
    {
        $auditRequest = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $this->resource->expects($this->once())->method('load')->with($auditRequest, 1);
        $this->resource->expects($this->once())->method('delete')->with($auditRequest);
        $this->auditRequestRepository->deleteById(1);
    }

    public function testGetList()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchResult = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $collection = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection::class);

        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);
        $collection->expects($this->once())->method('getSize')->willReturn(1);
        $collection->expects($this->once())->method('getItems')->willReturn([$this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class)]);

        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($searchResult);
        $this->assertEquals($searchResult, $this->auditRequestRepository->getList($searchCriteria));
    }

    public function testGetAuditsToBeRun()
    {
        $collection = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection::class);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())->method('addFieldToFilter')->with('execution_time', ['null' => true]);
        $this->assertEquals($collection, $this->auditRequestRepository->getAuditsToBeRun());
    }
}