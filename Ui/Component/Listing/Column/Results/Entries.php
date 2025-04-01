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

namespace Crealoz\EasyAudit\Ui\Component\Listing\Column\Results;

use Crealoz\EasyAudit\Api\EntryRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Entries extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function __construct(
        ContextInterface                              $context,
        UiComponentFactory                            $uiComponentFactory,
        protected readonly EntryRepositoryInterface   $entryRepository,
        protected readonly \Magento\Backend\Model\Url $backendUrl,
        array                                         $components = [],
        array                                         $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $items = $dataSource['data']['items'];
        foreach ($items as &$item) {
            $hasEntries = $this->entryRepository->hasEntries($item['result_id']);
            if ($hasEntries) {
                $html = '<a href="' . $this->backendUrl->getUrl('easyaudit/result/view', ['result_id' => $item['result_id']]) . '">';
                $html .= __('Details');
                $html .= '</a>';
                $item[$this->getData('name')] = $html;
            }
        }
        $dataSource['data']['items'] = $items;
        return $dataSource;
    }
}
