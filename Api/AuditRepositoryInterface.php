<?php

namespace Crealoz\EasyAudit\Api;

use Crealoz\EasyAudit\Api\Data\AuditInterface;
use Crealoz\EasyAudit\Api\Data\AuditSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface AuditRepositoryInterface
{
    /**
     * @param int $id
     * @return AuditInterface
     */
    public function getById(int $id): AuditInterface;

    /**
     * @param AuditInterface $audit
     * @return AuditInterface
     */
    public function save(AuditInterface $audit): AuditInterface;

    /**
     * @param AuditInterface $audit
     * @return void
     */
    public function delete(AuditInterface $audit): void;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return AuditSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AuditSearchResultsInterface;

    /**
     * @param int $id
     * @return void
     */
    public function deleteById(int $id): void;
}