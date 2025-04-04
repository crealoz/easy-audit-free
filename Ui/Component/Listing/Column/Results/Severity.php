<?php


namespace Crealoz\EasyAudit\Ui\Component\Listing\Column\Results;

use Crealoz\EasyAudit\Service\SeverityManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Severity extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        protected readonly SeverityManager $severityManager,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $severity = $this->severityManager->getSeverity($item['severity_id']);
                $item[$fieldName . '_html'] = '<span style="color: #' . $severity['color'] . ';">' . __($severity['level']) . '</span>';
            }
        }

        return $dataSource;
    }
}
