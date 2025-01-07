<?php

namespace Crealoz\EasyAudit\Api;

use Crealoz\EasyAudit\Api\Data\FileInterface;

interface FileRepositoryInterface
{


    /**
     * @param FileInterface $file
     * @return FileInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Crealoz\EasyAudit\Api\Data\FileInterface $file);

    /**
     * @param $id
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param FileInterface $file
     * @return true
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Crealoz\EasyAudit\Api\Data\FileInterface $file);

    /**
     * @param $requestId
     * @return mixed
     */
    public function getByRequestId($requestId);

    /**
     * @param $requestId
     * @return bool
     */
    public function hasFiles($requestId): bool;
}
