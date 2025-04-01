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


use Crealoz\EasyAudit\Api\Data\ResultInterface;
use Crealoz\EasyAudit\Model\ResourceModel\Result as ResultResource;
use Crealoz\EasyAudit\Model\ResourceModel\Result\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class ResultRepository implements \Crealoz\EasyAudit\Api\ResultRepositoryInterface
{


    public function __construct(
        private readonly ResultResource               $resource,
        private readonly ResultFactory                $resultFactory,
        private readonly CollectionFactory            $collectionFactory,
        private readonly SearchResultFactory          $searchResultFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly SearchCriteriaBuilder        $searchCriteriaBuilder
    )
    {

    }

    /**
     * @inheritDoc
     */
    public function save(\Crealoz\EasyAudit\Api\Data\ResultInterface $result)
    {
        try {
            $this->resource->save($result);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__("Could not save the Result: %1", $exception->getMessage()));
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getById($id): \Crealoz\EasyAudit\Api\Data\ResultInterface
    {
        $result = $this->resultFactory->create();
        $this->resource->load($result, $id);
        if (!$result->getId()) {
            throw new NoSuchEntityException(__("Result with id \"%1\" does not exist.", $id));
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function hasResults($requestId): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(ResultInterface::REQUEST_ID, $requestId)->create();
        $searchResults = $this->getList($searchCriteria);
        return $searchResults->getTotalCount() > 0;
    }

    /**
     * @inheritDoc
     */
    public function getByRequestId($requestId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(ResultInterface::REQUEST_ID, $requestId)->create();
        $searchResults = $this->getList($searchCriteria);
        return $searchResults->getItems();
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
    public function delete(\Crealoz\EasyAudit\Api\Data\ResultInterface $result)
    {
        try {
            $this->resource->delete($result);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__("Could not delete the Result: %1", $exception->getMessage()));
        }
        return true;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getByQueueId($queueId)
    {
        $result = $this->resultFactory->create();
        $this->resource->load($result, $queueId, ResultInterface::QUEUE_ID);
        if (!$result->getId()) {
            throw new NoSuchEntityException(__("Result with queue id \"%1\" does not exist.", $queueId));
        }
        return $result;
    }

}
