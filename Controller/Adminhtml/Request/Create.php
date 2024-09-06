<?php

namespace Crealoz\EasyAudit\Controller\Adminhtml\Request;

class Create extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Crealoz_EasyAudit::index';

    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        return $resultPage;
    }
}