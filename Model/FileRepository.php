<?php

namespace Crealoz\EasyAudit\Model;


use Crealoz\EasyAudit\Api\Data\FileInterface;
use Crealoz\EasyAudit\Model\ResourceModel\Request\File;
use Crealoz\EasyAudit\Model\ResourceModel\Request\File\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class FileRepository implements \Crealoz\EasyAudit\Api\FileRepositoryInterface
{


    public function __construct(
        private readonly File                         $resource,
        private readonly Request\FileFactory                  $fileFactory,
        private readonly CollectionFactory            $collectionFactory,
        private readonly SearchResultFactory          $searchResultFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly SearchCriteriaBuilder        $searchCriteriaBuilder
    )
    {

    }

    /**
     * @inheritdoc
     */
    public function save(FileInterface $file)
    {
        try {
            $this->resource->save($file);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__("Could not save the File: %1", $exception->getMessage()));
        }
        return $file;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        $file = $this->fileFactory->create();
        $this->resource->load($file, $id);
        if (!$file->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__("File with id \"%1\" does not exist.", $id));
        }
        return $file;
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function delete(FileInterface $file)
    {
        try {
            $this->resource->delete($file);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__("Could not delete the File: %1", $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getByRequestId($requestId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('request_id', $requestId)->create();
        $collection = $this->getList($searchCriteria);
        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function hasFiles($requestId): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('request_id', $requestId)->create();
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        return $collection->getSize() > 0;
    }
}
