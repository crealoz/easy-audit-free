<?php


namespace Crealoz\EasyAudit\Ui\DataProvider;

use Crealoz\EasyAudit\Service\PrManager;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\DirectoryList;

class Patch extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        private readonly DirectoryList $directoryList,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    protected function searchResultToOutput($searchResult)
    {
        return [
            'items' => [
                [
                    'id' => 0,
                    'patch_type' => PrManager::PATCH_TYPE_PATCH,
                    'relative_path' => $this->directoryList->getRoot()
                ]
            ],
            'totalRecords' => 1
        ];
    }
}
