<?php

namespace Crealoz\EasyAudit\Block\Adminhtml\Widget\Button\Result;

class Patch implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{

    public function getButtonData(): array
    {
        return [
            'label' => __('Request Audit'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
