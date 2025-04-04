<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Api\Data\TypeInterface;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Type as TypeResource;
use Crealoz\EasyAudit\Model\Result\Type;
use Crealoz\EasyAudit\Model\Result\TypeFactory;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Type\CollectionFactory;
use Crealoz\EasyAudit\Model\TypeRepository;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class TypeRepositoryTest extends TestCase
{
    private $resource;
    private $type;
    private $typeFactory;
    private $collectionFactory;
    private $searchResultFactory;
    private $collectionProcessor;
    private $typeRepository;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(TypeResource::class);
        $this->type = $this->createMock(Type::class);
        $this->typeFactory = $this->getMockBuilder(TypeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultFactory = $this->createMock(SearchResultFactory::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);

        $this->typeRepository = new TypeRepository(
            $this->resource,
            $this->typeFactory,
            $this->collectionFactory,
            $this->searchResultFactory,
            $this->collectionProcessor
        );
    }

    public function testSave()
    {
        $this->resource->expects($this->once())
            ->method('save')
            ->with($this->type);

        $result = $this->typeRepository->save($this->type);
        $this->assertSame($this->type, $result);
    }

    public function testSaveException()
    {
        $this->resource->method('save')
            ->willThrowException(new \Exception('Save error'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the Type: Save error');

        $this->typeRepository->save($this->type);
    }

    public function testGetById()
    {
        $this->typeFactory->method('create')
            ->willReturn($this->type);
        $this->resource->method('load')
            ->with($this->type, 1);
        $this->type->method('getId')
            ->willReturn(1);

        $result = $this->typeRepository->getById(1);
        $this->assertSame($this->type, $result);
    }

    public function testGetByIdException()
    {
        $this->typeFactory->method('create')
            ->willReturn($this->type);
        $this->resource->method('load')
            ->with($this->type, 1);
        $this->type->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Type with id "1" does not exist.');

        $this->typeRepository->getById(1);
    }

    public function testGetByType()
    {
        $this->typeFactory->method('create')
            ->willReturn($this->type);
        $this->resource->method('load')
            ->with($this->type, 'user', 'name');
        $this->type->method('getId')
            ->willReturn(1);

        $result = $this->typeRepository->getByType('user');
        $this->assertSame($this->type, $result);
    }

    public function testGetByTypeException()
    {
        $this->typeFactory->method('create')
            ->willReturn($this->type);
        $this->resource->method('load')
            ->with($this->type, 'unknown', 'name');
        $this->type->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Type with name "unknown" does not exist.');

        $this->typeRepository->getByType('unknown');
    }

    public function testDelete()
    {
        $this->resource->expects($this->once())
            ->method('delete')
            ->with($this->type);

        $result = $this->typeRepository->delete($this->type);
        $this->assertTrue($result);
    }

    public function testDeleteException()
    {
        $this->resource->method('delete')
            ->willThrowException(new \Exception('Delete error'));

        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessage('Could not delete the Type: Delete error');

        $this->typeRepository->delete($this->type);
    }

    public function testGetList()
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $collection = $this->createMock('\Crealoz\EasyAudit\Model\ResourceModel\Result\Type\Collection');

        $this->collectionFactory->method('create')
            ->willReturn($collection);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);

        $searchResult = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $this->searchResultFactory->method('create')
            ->willReturn($searchResult);

        $type1 = $this->createMock(TypeInterface::class);
        $type2 = $this->createMock(TypeInterface::class);
        $collection->method('getItems')->willReturn([$type1, $type2]);
        $collection->method('getSize')->willReturn(2);

        $searchResult->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $searchResult->expects($this->once())->method('setItems')->with([$type1, $type2]);
        $searchResult->expects($this->once())->method('setTotalCount')->with(2);

        $this->assertSame($searchResult, $this->typeRepository->getList($searchCriteria));
    }
}