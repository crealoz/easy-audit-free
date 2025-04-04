<?php

namespace Crealoz\EasyAudit\Api;

use Crealoz\EasyAudit\Api\Data\EntryInterface;
use Crealoz\EasyAudit\Api\Data\SubEntryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;

interface EntryRepositoryInterface
{

    /**
     * @param EntryInterface $entry
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function save(EntryInterface $entry): EntryInterface;

    /**
     * @param $id
     * @return EntryInterface
     */
    public function getById($id): EntryInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return EntryInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param EntryInterface $entry
     * @return bool
     */
    public function delete(EntryInterface $entry): bool;

    /**
     * @param $resultId
     * @return EntryInterface[]
     */
    public function getEntriesByResultId($resultId);

    /**
     * @param $resultId
     * @return bool
     */
    public function hasEntries($resultId): bool;
}
