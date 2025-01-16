<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest;

class AuditRequestRepositoryTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->resource = $this->createMock(AuditRequest::class);
        $auditRequest = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $this->requestFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\AuditRequestFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->requestFactory->method('create')->willReturn($auditRequest);
        $this->searchResultFactory = $this->createMock(\Magento\Framework\Api\Search\SearchResultFactory::class);
        $this->collectionProcessor = $this->createMock(\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class);
        $this->collectionFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

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
        $auditRequest1 = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $auditRequest2 = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $auditRequest3 = $this->createMock(\Crealoz\EasyAudit\Model\AuditRequest::class);
        $auditRequestArray = [$auditRequest1, $auditRequest2, $auditRequest3];
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchResult = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $searchResult->expects($this->once())->method('setItems')->with($auditRequestArray);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($searchResult);
        $collection = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection::class);
        $collection->expects($this->once())->method('getSize')->willReturn(1);
        $collection->expects($this->once())->method('getItems')->willReturn($auditRequestArray);

        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);

        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($searchResult);
        $this->assertEquals($searchResult, $this->auditRequestRepository->getList($searchCriteria));
    }

    public function testGetAuditsToBeRun()
    {
        $collection = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection::class);
        $collection->expects($this->once())->method('addFieldToFilter')->with('execution_time', ['null' => true]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->assertEquals($collection, $this->auditRequestRepository->getAuditsToBeRun());
    }
}