<?php


namespace Crealoz\EasyAudit\Model\Result\Severity;

use Crealoz\EasyAudit\Model\ResourceModel\Result\Severity\CollectionFactory;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    public function __construct(
        protected readonly CollectionFactory $severityCollectionFactory
    )
    {
    }

    public function toOptionArray(): array
    {
        $options = [];
        $collection = $this->severityCollectionFactory->create();
        $severities = $collection->getItems();
        foreach ($severities as $severity) {
            /** @var \Crealoz\EasyAudit\Model\Result\Severity $severity */
            $options[] = [
                'color' => $severity->getColor(),
                'label' => $severity->getLevel(),
                'value' => $severity->getId()
            ];
        }
        return $options;

    }
}
