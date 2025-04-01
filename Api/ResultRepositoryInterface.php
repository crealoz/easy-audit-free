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

interface ResultRepositoryInterface 
{

    /**
     * @param Data\ResultInterface $result
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Crealoz\EasyAudit\Api\Data\ResultInterface $result);

    /**
     * @param $id
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id): \Crealoz\EasyAudit\Api\Data\ResultInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\ResultInterface $result
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Crealoz\EasyAudit\Api\Data\ResultInterface $result);

    /**
     * Checks if the request has results
     * @param $requestId
     * @return bool
     */
    public function hasResults($requestId): bool;

    /**
     * Returns the results for a request
     * @param $requestId
     * @return mixed
     */
    public function getByRequestId($requestId);
}
