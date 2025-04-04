<?php


namespace Crealoz\EasyAudit\Model\Result\PatchType;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['label' => 'Patch', 'value' => '1'],
            ['label' => 'Git', 'value' => '2'],
        ];
    }
}
