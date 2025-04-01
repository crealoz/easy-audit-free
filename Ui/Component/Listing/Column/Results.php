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

namespace Crealoz\EasyAudit\Ui\Component\Listing\Column;

use Crealoz\EasyAudit\Api\ResultRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Results extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param ResultRepositoryInterface $resultRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface                           $context,
        UiComponentFactory                         $uiComponentFactory,
        private readonly \Magento\Backend\Model\Url $backendUrl,
        private readonly ResultRepositoryInterface $resultRepository,
        array                                      $components = [],
        array                                      $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $items = $dataSource['data']['items'];
        foreach ($items as &$item) {
            if (isset($item['request_id']) && $this->resultRepository->hasResults($item['request_id'])) {
                $html = '<a href="' . $this->backendUrl->getUrl('easyaudit/result/index', ['request_id' => $item['request_id']]) . '">';
                $html .= __('View Results');
                $html .= '</a>';
                $item[$this->getData('name')] = $html;
            }
        }
        $dataSource['data']['items'] = $items;
        return $dataSource;
    }
}
