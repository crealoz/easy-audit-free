<?php
/**
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */

namespace Crealoz\EasyAudit\Model;


use Crealoz\EasyAudit\Api\Data\EntryInterface;
use Crealoz\EasyAudit\Api\Data\EntrySearchCriteriaInterfaceFactory;
use Crealoz\EasyAudit\Model\Result\EntryFactory;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry;
use Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class EntryRepository implements \Crealoz\EasyAudit\Api\EntryRepositoryInterface
{


    public function __construct(
        private readonly Entry                               $resource,
        private readonly EntryFactory                        $entryFactory,
        private readonly CollectionFactory                   $collectionFactory,
        private readonly SearchResultFactory          $searchResultFactory,
        private readonly CollectionProcessorInterface        $collectionProcessor,
        private readonly SearchCriteriaBuilder        $searchCriteriaBuilder
    )
    {

    }

    /**
     * @inheritDoc
     */
    public function save(EntryInterface $entry): EntryInterface
    {
        try {
            $this->resource->save($entry);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__("Could not save the Entry: %1", $exception->getMessage()));
        }
        return $entry;
    }

    /**
     * @inheritDoc
     */
    public function getById($id): EntryInterface
    {
        $entry = $this->entryFactory->create();
        $this->resource->load($entry, $id);
        if (!$entry->getId()) {
            throw new NoSuchEntityException(__("Entry with id \"%1\" does not exist.", $id));
        }
        return $entry;
    }

    /**
     * @inheritDoc
     */
    public function getEntriesByResultId($resultId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(EntryInterface::RESULT_ID, $resultId)->create();
        return $this->getList($searchCriteria)->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
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
    public function delete(EntryInterface $entry): bool
    {
        try {
            $this->resource->delete($entry);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__("Could not delete the Entry: %1", $exception->getMessage()));
        }
        return true;
    }

    public function hasEntries($resultId): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(EntryInterface::RESULT_ID, $resultId)->create();
        return $this->getList($searchCriteria)->getTotalCount() > 0;
    }

    public function getSubEntries($entryId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(EntryInterface::ENTRY_ID, $entryId)->create();
        return $this->getList($searchCriteria)->getItems();
    }

}
