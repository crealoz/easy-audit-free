<?php


namespace Crealoz\EasyAudit\Ui\Component\Listing\Column\Results;

use Crealoz\EasyAudit\Service\SeverityManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Summary extends \Magento\Ui\Component\Listing\Columns\Column
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

    /**
     * Cuts summary to 300 characters
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $summary = $item['summary'];
                if (strlen((string) $summary) > 150) {
                    $summary = mb_substr((string) $summary, 0, 150) . '...';
                }
                $item[$fieldName] = $summary;
            }
        }

        return $dataSource;
    }
}
