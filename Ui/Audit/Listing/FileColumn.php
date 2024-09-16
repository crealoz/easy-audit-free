<?php

namespace Crealoz\EasyAudit\Ui\Audit\Listing;

use Magento\Framework\Url;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class FileColumn extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        private readonly \Magento\Backend\Model\Url $backendUrl,
        array              $components = [],
        array              $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $items = $dataSource['data']['items'];
        foreach ($items as &$item) {
            if (empty($item['filepath'])) {
                continue;
            }
            $url = $this->backendUrl->getUrl('easyaudit/request/download', ['filename' => $item['filepath'], '_secure' => true]);
            $item['link'] = $url;
        }
        $dataSource['data']['items'] = $items;
        return $dataSource;
    }
}