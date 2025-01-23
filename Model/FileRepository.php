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


    /**
     * @readonly
     */
    private File $resource;
    /**
     * @readonly
     */
    private Request\FileFactory $fileFactory;
    /**
     * @readonly
     */
    private CollectionFactory $collectionFactory;
    /**
     * @readonly
     */
    private SearchResultFactory $searchResultFactory;
    /**
     * @readonly
     */
    private CollectionProcessorInterface $collectionProcessor;
    /**
     * @readonly
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    public function __construct(File                         $resource, Request\FileFactory                  $fileFactory, CollectionFactory            $collectionFactory, SearchResultFactory          $searchResultFactory, CollectionProcessorInterface $collectionProcessor, SearchCriteriaBuilder        $searchCriteriaBuilder)
    {
        $this->resource = $resource;
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

    public function hasFiles($requestId): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('request_id', $requestId)->create();
        $collection = $this->getList($searchCriteria);
        return $collection->getTotalCount() > 0;
    }
}
