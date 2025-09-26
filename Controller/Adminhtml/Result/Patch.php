<?php


namespace Crealoz\EasyAudit\Controller\Adminhtml\Result;

use Crealoz\EasyAudit\Api\ResultRepositoryInterface;
use Magento\Backend\App\Action\Context;

class Patch extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Crealoz_EasyAudit::view';

    public function __construct(
        Context $context,
        protected readonly ResultRepositoryInterface $auditResultRepository
    )
    {
        parent::__construct($context);
    }


    public function execute()
    {
        $auditResultId = $this->getRequest()->getParam('result_id');
        if (!$auditResultId) {
            $this->messageManager->addErrorMessage(__('Entries cannot be viewed without a result ID.'));
            return $this->_redirect('*/result/index');
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu("Crealoz_EasyAudit::index");
        $auditResult = $this->auditResultRepository->getById($auditResultId);
        $resultPage->addBreadcrumb(__('Audit Results'), __('Audit Results'));
        $resultPage->getConfig()->getTitle()->prepend($auditResult->getTitle());
        return $resultPage;
    }
}
