<?php


namespace Crealoz\EasyAudit\Api;

use Crealoz\EasyAudit\Api\Data\SeverityInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SeverityRepositoryInterface
{

    /**
     * @param SeverityInterface $severity
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(SeverityInterface $severity);

    /**
     * @param $id
     * @return SeverityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id) : SeverityInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param SeverityInterface $severity
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(SeverityInterface $severity): bool;

    /**
     * @param string $level
     * @return SeverityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByLevel(string $level) : SeverityInterface;
}
