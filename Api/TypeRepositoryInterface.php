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

use Crealoz\EasyAudit\Api\Data\TypeInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface TypeRepositoryInterface
{

    /**
     * @param TypeInterface $type
     * @return TypeInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(TypeInterface $type): TypeInterface;

    /**
     * @param $id
     * @return TypeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id): TypeInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param TypeInterface $type
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(TypeInterface $type): bool;

    /**
     * @param string $type
     * @return TypeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByType(string $type): TypeInterface;
}
