<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestSearchResultsInterface;
use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection;
use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class AuditRequestRepository implements AuditRequestRepositoryInterface
{
    public function __construct(
        protected \Crealoz\EasyAudit\Model\ResourceModel\AuditRequest $auditRequestResource,
        protected \Crealoz\EasyAudit\Model\AuditRequestFactory $auditRequestFactory,
        private readonly CollectionFactory $auditRequestCollectionFactory
    )
    {
    }

    /**
     * @param AuditRequestInterface $auditRequest
     * @return void
     * @throws CouldNotSaveException
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
     * @param int $id
     * @return AuditRequestInterface
     */
    public function getById(int $id): AuditRequestInterface
    {
        $auditRequest = $this->auditRequestFactory->create();
        $this->auditRequestResource->load($auditRequest, $id);
        return $auditRequest;
    }

    /**
     * @param AuditRequestInterface $auditRequest
     * @return void
     * @throws \Exception
     */
    public function delete(AuditRequestInterface $auditRequest): void
    {
        $this->auditRequestResource->delete($auditRequest);
    }

    /**
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteById(int $id): void
    {
        $auditRequest = $this->getById($id);
        $this->delete($auditRequest);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return AuditRequestSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AuditRequestSearchResultsInterface
    {
        $auditRequestCollection = $this->auditRequestFactory->create()->getCollection();
        $auditRequestCollection->addFilters($searchCriteria);
        return $auditRequestCollection->getItems();
    }


    public function getAuditsToBeRun(): Collection
    {
        $auditRequestCollection = $this->auditRequestCollectionFactory->create();
        $auditRequestCollection->addFieldToFilter('execution_time', ['null' => true]);
        return $auditRequestCollection;
    }
}
