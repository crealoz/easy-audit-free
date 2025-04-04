<?php


namespace Crealoz\EasyAudit\Controller\Adminhtml\Result;


class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Crealoz_EasyAudit::view';


    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu("Crealoz_EasyAudit::index");
        $resultPage->getConfig()->getTitle()->prepend(__('Audit Results'));
        return $resultPage;
    }

}
