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
