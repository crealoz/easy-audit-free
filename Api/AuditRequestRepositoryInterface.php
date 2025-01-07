<?php

namespace Crealoz\EasyAudit\Api;

use Crealoz\EasyAudit\Api\Data\AuditInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestSearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;

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
     * @throws CouldNotSaveException
     */
    public function save(AuditRequestInterface $auditRequest): void;

    /**
     * @param AuditRequestInterface $auditRequest
     * @return void
     * @throws \Exception
     */
    public function delete(AuditRequestInterface $auditRequest): void;

    /**
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteById(int $id): void;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * @return AuditInterface[]
     */
    public function getAuditsToBeRun(): Collection;
}