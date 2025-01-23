<?php

namespace Crealoz\EasyAudit\Ui\Audit\Listing;

use Crealoz\EasyAudit\Api\FileRepositoryInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class FileColumn extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @readonly
     */
    private UrlInterface $backendUrl;
    /**
     * @readonly
     */
    private FileRepositoryInterface $fileRepository;
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $backendUrl,
        FileRepositoryInterface $fileRepository,
        array              $components = [],
        array              $data = []
    )
    {
        $this->backendUrl = $backendUrl;
        $this->fileRepository = $fileRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $items = $dataSource['data']['items'];
        foreach ($items as &$item) {
            if (!$this->fileRepository->hasFiles($item['request_id'])) {
                continue;
            }
            $url = $this->backendUrl->getUrl('easyaudit/request/download', ['request_id' => $item['request_id'], '_secure' => true]);
            $item['files'] = __('Download files(s)');
            $item['link'] = $url;
        }
        $dataSource['data']['items'] = $items;
        return $dataSource;
    }
}