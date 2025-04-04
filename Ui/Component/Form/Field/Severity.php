<?php


namespace Crealoz\EasyAudit\Ui\Component\Form\Field;

use Crealoz\EasyAudit\Service\SeverityManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Severity extends \Magento\Ui\Component\Form\Field
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly SeverityManager $severityManager,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['general'])) {
            foreach ($dataSource['data']['general'] as $name => $value) {
                if ($name === 'severity_id') {
                    $severity = $this->severityManager->getSeverity($value);
                    $dataSource['data']['general']['severity'] = __($severity['level']);
                }
            }
        }

        return $dataSource;
    }
}
