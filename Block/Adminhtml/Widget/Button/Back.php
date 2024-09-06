<?php

namespace Crealoz\EasyAudit\Block\Adminhtml\Widget\Button;

use Magento\Backend\Block\Widget\Button;

class Back extends Button implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{

    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'sort_order' => 10,
        ];
    }
}