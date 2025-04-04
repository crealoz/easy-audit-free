<?php

namespace Crealoz\EasyAudit\Model;


use Crealoz\EasyAudit\Api\Data\SeverityInterface;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Severity;
use Crealoz\EasyAudit\Model\Result\SeverityFactory;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Severity\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class SeverityRepository implements \Crealoz\EasyAudit\Api\SeverityRepositoryInterface
{


    public function __construct(
        private readonly Severity                     $resource,
        private readonly SeverityFactory              $severityFactory,
        private readonly CollectionFactory            $collectionFactory,
        private readonly SearchResultFactory          $searchResultFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    )
    {

    }

    /**
     * @inheritDoc
     */
    public function save(SeverityInterface $severity)
    {
        try {
            $this->resource->save($severity);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__("Could not save the Severity: %1", $exception->getMessage()));
        }
        return $severity;
    }


    /**
     * @inheritDoc
     */
    public function getById($id): SeverityInterface
    {
        $severity = $this->severityFactory->create();
        $this->resource->load($severity, $id);
        if (!$severity->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__("Severity with id \"%1\" does not exist.", $id));
        }
        return $severity;
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
    public function delete(SeverityInterface $severity): bool
    {
        try {
            $this->resource->delete($severity);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__("Could not delete the Severity: %1", $exception->getMessage()));
        }
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getByLevel(string $level): SeverityInterface
    {
        $severity = $this->severityFactory->create();
        $this->resource->load($severity, $level, 'level');
        if (!$severity->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__("Severity with level \"%1\" does not exist.", $level));
        }
        return $severity;
    }
}
