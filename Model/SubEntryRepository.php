<?php


namespace Crealoz\EasyAudit\Model;


use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntryFactory;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class SubEntryRepository implements \Crealoz\EasyAudit\Api\SubEntryRepositoryInterface
{


    public function __construct(
        private readonly SubEntry                     $resource,
        private readonly SubEntryFactory              $subEntryFactory,
        private readonly CollectionFactory            $collectionFactory,
        private readonly SearchResultFactory          $searchResultFactory,
        private readonly SearchCriteriaBuilder        $searchCriteriaBuilder,
        private readonly CollectionProcessorInterface $collectionProcessor
    )
    {

    }

    public function save(\Crealoz\EasyAudit\Api\Data\SubEntryInterface $subEntry)
    {
        try {
            $this->resource->save($subEntry);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__("Could not save the SubEntry: %1", $exception->getMessage()));
        }
        return $subEntry;
    }

    public function getById($id)
    {
        $subEntry = $this->subEntryFactory->create();
        $this->resource->load($subEntry, $id);
        if (!$subEntry->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__("SubEntry with id \"%1\" does not exist.", $id));
        }
        return $subEntry;
    }

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

    public function delete(\Crealoz\EasyAudit\Api\Data\SubEntryInterface $subEntry)
    {
        try {
            $this->resource->delete($subEntry);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__("Could not delete the SubEntry: %1", $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSubEntriesByEntryId($entryId): array
    {
        return $this->getList($this->searchCriteriaBuilder->addFilter('entry_id', $entryId)->create())->getItems();
    }

    /**
     * @inheritDoc
     */
    public function hasSubEntries($entryId) : bool
    {
        return $this->getList($this->searchCriteriaBuilder->addFilter('entry_id', $entryId)->create())->getTotalCount() > 0;
    }

}
