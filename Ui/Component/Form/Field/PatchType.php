<?php


namespace Crealoz\EasyAudit\Ui\Component\Form\Field;

use Crealoz\EasyAudit\Service\PrManager;

class PatchType implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            ['label' => 'Patch', 'value' => PrManager::PATCH_TYPE_PATCH],
            ['label' => 'Git', 'value' => PrManager::PATCH_TYPE_GIT],
        ];
    }
}
