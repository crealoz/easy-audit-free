<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\AuditRepositoryInterface;
use Crealoz\EasyAudit\Api\Data\AuditInterface;
use Crealoz\EasyAudit\Api\Data\AuditSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class AuditRepository implements AuditRepositoryInterface
{
    public function __construct(
        protected \Crealoz\EasyAudit\Model\ResourceModel\Audit $auditResource,
        protected \Crealoz\EasyAudit\Model\AuditFactory $auditFactory
    )
    {
    }

    /**
     * @param int $id
     * @return AuditInterface
     */
    public function getById(int $id): AuditInterface
    {
        $audit = $this->auditFactory->create();
        $this->auditResource->load($audit, $id);
        return $audit;
    }

    /**
     * @param AuditInterface $audit
     * @return AuditInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(AuditInterface $audit): AuditInterface
    {
        $this->auditResource->save($audit);
        return $audit;
    }

    /**
     * @param AuditInterface $audit
     * @return void
     * @throws \Exception
     */
    public function delete(AuditInterface $audit): void
    {
        $this->auditResource->delete($audit);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return AuditSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AuditSearchResultsInterface
    {
        $collection = $this->auditFactory->create()->getCollection();
        $collection->addFilters($searchCriteria);
        return $collection->getSearchResults();
    }

    /**
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteById(int $id): void
    {
        $audit = $this->getById($id);
        $this->delete($audit);
    }
}
