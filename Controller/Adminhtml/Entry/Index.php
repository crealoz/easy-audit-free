<?php


namespace Crealoz\EasyAudit\Controller\Adminhtml\Entry;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Crealoz_EasyAudit::view';


    public function execute()
    {
        $resultId = $this->getRequest()->getParam('result_id');
        if (!$resultId) {
            $this->messageManager->addErrorMessage(__('Entries cannot be viewed without a result ID.'));
            return $this->_redirect('*/result/index');
        }
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu("Crealoz_EasyAudit::index");
        return $resultPage;
    }

}
