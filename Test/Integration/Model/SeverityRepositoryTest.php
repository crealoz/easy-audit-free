<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Api\Data\SeverityInterface;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Severity as SeverityResource;
use Crealoz\EasyAudit\Model\Result\Severity;
use Crealoz\EasyAudit\Model\Result\SeverityFactory;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Severity\CollectionFactory;
use Crealoz\EasyAudit\Model\SeverityRepository;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class SeverityRepositoryTest extends TestCase
{
    private $resource;
    private $severityFactory;
    private $collectionFactory;
    private $searchResultFactory;
    private $collectionProcessor;
    private $severityRepository;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(SeverityResource::class);
        $this->severity = $this->createMock(Severity::class);
        $this->severityFactory = $this->getMockBuilder(SeverityFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultFactory = $this->createMock(SearchResultFactory::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);

        $this->severityRepository = new SeverityRepository(
            $this->resource,
            $this->severityFactory,
            $this->collectionFactory,
            $this->searchResultFactory,
            $this->collectionProcessor
        );
    }

    public function testSave()
    {
        $this->resource->expects($this->once())
            ->method('save')
            ->with($this->severity);

        $result = $this->severityRepository->save($this->severity);
        $this->assertSame($this->severity, $result);
    }

    public function testSaveException()
    {
        $this->resource->method('save')
            ->willThrowException(new \Exception('Save error'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the Severity: Save error');

        $this->severityRepository->save($this->severity);
    }

    public function testGetById()
    {
        $this->severityFactory->method('create')
            ->willReturn($this->severity);
        $this->resource->method('load')
            ->with($this->severity, 1);
        $this->severity->method('getId')
            ->willReturn(1);

        $result = $this->severityRepository->getById(1);
        $this->assertSame($this->severity, $result);
    }

    public function testGetByIdException()
    {
        $this->severityFactory->method('create')
            ->willReturn($this->severity);
        $this->resource->method('load')
            ->with($this->severity, 1);
        $this->severity->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Severity with id "1" does not exist.');

        $this->severityRepository->getById(1);
    }

    public function testGetByLevel()
    {
        $this->severityFactory->method('create')
            ->willReturn($this->severity);
        $this->resource->method('load')
            ->with($this->severity, 'high', 'level');
        $this->severity->method('getId')
            ->willReturn(1);

        $result = $this->severityRepository->getByLevel('high');
        $this->assertSame($this->severity, $result);
    }

    public function testGetByLevelException()
    {
        $this->severityFactory->method('create')
            ->willReturn($this->severity);
        $this->resource->method('load')
            ->with($this->severity, 'unknown', 'level');
        $this->severity->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Severity with level "unknown" does not exist.');

        $this->severityRepository->getByLevel('unknown');
    }

    public function testDelete()
    {
        $this->resource->expects($this->once())
            ->method('delete')
            ->with($this->severity);

        $result = $this->severityRepository->delete($this->severity);
        $this->assertTrue($result);
    }

    public function testDeleteException()
    {
        $this->resource->method('delete')
            ->willThrowException(new \Exception('Delete error'));

        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessage('Could not delete the Severity: Delete error');

        $this->severityRepository->delete($this->severity);
    }

    public function testGetList()
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $collection = $this->createMock('\Crealoz\EasyAudit\Model\ResourceModel\Result\Severity\Collection');
        
        $this->collectionFactory->method('create')
            ->willReturn($collection);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        
        $searchResult = $this->createMock('\Magento\Framework\Api\Search\SearchResultInterface');
        $this->searchResultFactory->method('create')
            ->willReturn($searchResult);
        
        $severity1 = $this->createMock(SeverityInterface::class);
        $severity2 = $this->createMock(SeverityInterface::class);
        $collection->method('getItems')
            ->willReturn([$severity1, $severity2]);
        $collection->method('getSize')
            ->willReturn(2);
        
        $searchResult->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);
        $searchResult->expects($this->once())
            ->method('setItems')
            ->with([$severity1, $severity2]);
        $searchResult->expects($this->once())
            ->method('setTotalCount')
            ->with(2);
        
        $result = $this->severityRepository->getList($searchCriteria);
        $this->assertSame($searchResult, $result);
    }
}