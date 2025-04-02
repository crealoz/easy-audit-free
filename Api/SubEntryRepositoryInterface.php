<?php


namespace Crealoz\EasyAudit\Api;

interface SubEntryRepositoryInterface 
{

    public function save(\Crealoz\EasyAudit\Api\Data\SubEntryInterface $subEntry);
    public function getById($id);
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
    public function delete(\Crealoz\EasyAudit\Api\Data\SubEntryInterface $subEntry);

    /**
     * @param $entryId
     * @return \Crealoz\EasyAudit\Api\Data\SubEntryInterface[]
     */
    public function getSubEntriesByEntryId($entryId): array;

    /**
     * @param $entryId
     * @return bool
     */
    public function hasSubEntries($entryId): bool;
}
