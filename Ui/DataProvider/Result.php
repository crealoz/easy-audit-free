<?php


namespace Crealoz\EasyAudit\Ui\DataProvider;

use Crealoz\EasyAudit\Service\PrManager;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\DirectoryList;

class Result extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
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

    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];
        $count = 0;

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $itemData = $item->getData();
            $itemData['bug_trackers'] = '0';
            if ($item['github_issue'] !== null) {
                $itemData['bug_trackers'] = '1';
            } elseif ($item['jira_issue'] !== null) {
                $itemData['bug_trackers'] = '1';
            }
            $itemData['relative_path'] = $this->directoryList->getRoot();
            $itemData['patch_type'] = PrManager::PATCH_TYPE_PATCH;
            $arrItems['items'][] = $itemData;
            $count++;
        }

        $arrItems['totalRecords'] = $count;

        return $arrItems;
    }
}
