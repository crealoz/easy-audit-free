<?php


namespace Crealoz\EasyAudit\Controller\Adminhtml\Result;

use Crealoz\EasyAudit\Service\PrManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class ValidatePr extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Crealoz_EasyAudit::view';

    public function __construct(
        Context $context,
        protected readonly PrManager $prManager,
    )
    {
        parent::__construct($context);
    }
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultId = $this->getRequest()->getParam('result_id');
        $path = $this->getRequest()->getParam('path');
        $patchType = $this->getRequest()->getParam('patch_type');
        $errors = $this->prManager->validatePrRequest($resultId, $path, $patchType);
        $resultJson->setData($errors);
        return $resultJson;
    }
}
