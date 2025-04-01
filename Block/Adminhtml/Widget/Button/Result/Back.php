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

namespace Crealoz\EasyAudit\Block\Adminhtml\Widget\Button\Result;

use Crealoz\EasyAudit\Api\ResultRepositoryInterface;
use Magento\Backend\Block\Widget\Context;

class Back implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function __construct(
        protected readonly Context $context,
        protected readonly ResultRepositoryInterface $auditResultRepository,
    ) {

    }

    public function getButtonData()
    {
        $requestResultId = $this->context->getRequest()->getParam('result_id');
        $auditResult = $this->auditResultRepository->getById($requestResultId);
        $requestId = $auditResult->getRequestId();
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->context->getUrlBuilder()->getUrl('*/*/index', ['request_id' => $requestId])),
            'class' => 'back'
        ];
    }
}
{

}
