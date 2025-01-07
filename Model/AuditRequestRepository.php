<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest as AuditRequestResource;
use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection;
use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class AuditRequestRepository implements AuditRequestRepositoryInterface
{
    public function __construct(
        private readonly AuditRequestResource         $auditRequestResource,
        private readonly AuditRequestFactory          $auditRequestFactory,
        private readonly SearchResultFactory          $searchResultFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly CollectionFactory            $auditRequestCollectionFactory
    )
    {
    }

    /**
     * @inheritdoc 
     */
    public function save(AuditRequestInterface $auditRequest): void
    {
        try {
            $this->auditRequestResource->save($auditRequest);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): AuditRequestInterface
    {
        $auditRequest = $this->auditRequestFactory->create();
        $this->auditRequestResource->load($auditRequest, $id);
        return $auditRequest;
    }

    /**
     * @inheritdoc
     */
    public function delete(AuditRequestInterface $auditRequest): void
    {
        $this->auditRequestResource->delete($auditRequest);
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): void
    {
        $auditRequest = $this->getById($id);
        $this->delete($auditRequest);
    }
    
    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $auditRequestCollection = $this->auditRequestCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $auditRequestCollection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($auditRequestCollection->getItems());
        $searchResults->setTotalCount($auditRequestCollection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getAuditsToBeRun(): Collection
    {
        $auditRequestCollection = $this->auditRequestCollectionFactory->create();
        $auditRequestCollection->addFieldToFilter('execution_time', ['null' => true]);
        return $auditRequestCollection;
    }
}
