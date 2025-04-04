<?php


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
