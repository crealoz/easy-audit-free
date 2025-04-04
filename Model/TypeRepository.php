<?php


namespace Crealoz\EasyAudit\Model;


use Crealoz\EasyAudit\Api\Data\TypeInterface;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Type;
use Crealoz\EasyAudit\Model\Result\TypeFactory;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Type\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class TypeRepository implements \Crealoz\EasyAudit\Api\TypeRepositoryInterface
{


    public function __construct(
        private readonly Type                         $resource,
        private readonly TypeFactory                  $typeFactory,
        private readonly CollectionFactory            $collectionFactory,
        private readonly SearchResultFactory          $searchResultFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    )
    {

    }

    /**
     * @inheritDoc
     */
    public function save(TypeInterface $type): TypeInterface
    {
        try {
            $this->resource->save($type);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__("Could not save the Type: %1", $exception->getMessage()));
        }
        return $type;
    }

    /**
     * @inheritDoc
     */
    public function getById($id): TypeInterface
    {
        $type = $this->typeFactory->create();
        $this->resource->load($type, $id);
        if (!$type->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__("Type with id \"%1\" does not exist.", $id));
        }
        return $type;
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(TypeInterface $type): bool
    {
        try {
            $this->resource->delete($type);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__("Could not delete the Type: %1", $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getByType(string $typeLabel): TypeInterface
    {
        $type = $this->typeFactory->create();
        $this->resource->load($type, $typeLabel, 'name');
        if (!$type->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__("Type with name \"%1\" does not exist.", $typeLabel));
        }
        return $type;
    }
}
