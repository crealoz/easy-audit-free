<?php

namespace Crealoz\EasyAudit\Block\Adminhtml\Widget\Button;

use Magento\Backend\Block\Widget\Button;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save extends Button implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}