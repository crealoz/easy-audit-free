<?php

namespace Crealoz\EasyAudit\Api;

use Crealoz\EasyAudit\Api\Data\AuditInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestSearchResultsInterface;

interface AuditRequestRepositoryInterface
{
    /**
     * @param int $id
     * @return AuditRequestInterface
     */
    public function getById(int $id): AuditRequestInterface;

    /**
     * @param AuditRequestInterface $auditRequest
     * @return void
     */
    public function save(AuditRequestInterface $auditRequest): void;

    /**
     * @param AuditRequestInterface $auditRequest
     * @return void
     */
    public function delete(AuditRequestInterface $auditRequest): void;

    /**
     * @param int $id
     * @return void
     */
    public function deleteById(int $id): void;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return AuditRequestSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AuditRequestSearchResultsInterface;

    /**
     * @return AuditInterface[]
     */
    public function getAuditsToBeRun(): Collection;
}