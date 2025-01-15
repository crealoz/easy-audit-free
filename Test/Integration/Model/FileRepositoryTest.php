<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Model\FileRepository;
use Crealoz\EasyAudit\Model\ResourceModel\Request\File;
use Crealoz\EasyAudit\Model\ResourceModel\Request\File\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class FileRepositoryTest extends TestCase
{
    private $resource;
    private $fileFactory;
    private $collectionFactory;
    private $searchResultFactory;
    private $collectionProcessor;
    private $searchCriteriaBuilder;
    private $fileRepository;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(File::class);
        $this->fileFactory = $this->getMockBuilder(\Crealoz\EasyAudit\Model\Request\FileFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock()
        ;
        $this->collectionFactory = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\Request\File\CollectionFactory::class);
        $this->searchResultFactory = $this->createMock(\Magento\Framework\Api\Search\SearchResultFactory::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);

        $this->fileRepository = new FileRepository(
            $this->resource,
            $this->fileFactory,
            $this->collectionFactory,
            $this->searchResultFactory,
            $this->collectionProcessor,
            $this->searchCriteriaBuilder
        );
    }

    public function testSave()
    {
        $file = $this->createMock(\Crealoz\EasyAudit\Model\Request\File::class);
        $this->resource->expects($this->once())->method('save')->with($file);

        $this->assertSame($file, $this->fileRepository->save($file));
    }

    public function testSaveException()
    {
        $file = $this->createMock(\Crealoz\EasyAudit\Model\Request\File::class);
        $this->resource->method('save')->willThrowException(new \Exception('Error'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the File: Error');

        $this->fileRepository->save($file);
    }

    public function testGetById()
    {
        $file = $this->createMock(\Crealoz\EasyAudit\Model\Request\File::class);
        $this->fileFactory->method('create')->willReturn($file);
        $this->resource->method('load')->with($file, 1);
        $file->method('getId')->willReturn(1);

        $this->assertSame($file, $this->fileRepository->getById(1));
    }

    public function testGetByIdException()
    {
        $file = $this->createMock(\Crealoz\EasyAudit\Model\Request\File::class);
        $this->fileFactory->method('create')->willReturn($file);
        $this->resource->method('load')->with($file, 1);
        $file->method('getId')->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('File with id "1" does not exist.');

        $this->fileRepository->getById(1);
    }

    public function testGetList()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $collection = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\Request\File\Collection::class);
        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);

        $this->collectionFactory->method('create')->willReturn($collection);
        $this->collectionProcessor->method('process')->with($searchCriteria, $collection);
        $this->searchResultFactory->method('create')->willReturn($searchResults);
        $collection->method('getItems')->willReturn(['item1', 'item2']);
        $collection->method('getSize')->willReturn(2);

        $searchResults->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $searchResults->expects($this->once())->method('setItems')->with(['item1', 'item2']);
        $searchResults->expects($this->once())->method('setTotalCount')->with(2);

        $this->assertSame($searchResults, $this->fileRepository->getList($searchCriteria));
    }

    public function testDelete()
    {
        $file = $this->createMock(\Crealoz\EasyAudit\Model\Request\File::class);
        $this->resource->expects($this->once())->method('delete')->with($file);

        $this->assertTrue($this->fileRepository->delete($file));
    }

    public function testDeleteException()
    {
        $file = $this->createMock(\Crealoz\EasyAudit\Model\Request\File::class);
        $this->resource->method('delete')->willThrowException(new \Exception('Error'));

        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessage('Could not delete the File: Error');

        $this->fileRepository->delete($file);
    }

    public function testGetByRequestId()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $collection = $this->createMock(File\Collection::class);
        $collection->method('getItems')->willReturn(['file1', 'file2']);

        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $searchResults->method('getItems')->willReturn(['file1', 'file2']);

        $this->searchCriteriaBuilder->method('addFilter')->with('request_id', 1)->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $this->collectionFactory->method('create')->willReturn($collection);
        $this->collectionProcessor->method('process')->with($searchCriteria, $collection)->willReturnSelf();
        $this->searchResultFactory->method('create')->willReturn($searchResults);

        $this->assertSame(['file1', 'file2'], $this->fileRepository->getByRequestId(1));
    }

    public function testHasFiles()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $collection = $this->createMock(File\Collection::class);

        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $searchResults->method('getTotalCount')->willReturn(2);

        $this->searchCriteriaBuilder->method('addFilter')->with('request_id', 1)->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $this->searchResultFactory->method('create')->willReturn($searchResults);
        $collection->method('getItems')->willReturn(['file1', 'file2']);
        $this->collectionFactory->method('create')->willReturn($collection);

        $this->assertTrue($this->fileRepository->hasFiles(1));
    }
}