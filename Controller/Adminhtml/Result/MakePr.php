<?php


namespace Crealoz\EasyAudit\Controller\Adminhtml\Result;

use Crealoz\EasyAudit\Service\PrManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class MakePr extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
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
        $data = $this->getRequest()->getParam('data');
        if (!is_array($data)) {
            $this->messageManager->addErrorMessage(__('Invalid data.'));
            return $resultJson;
        }
        if (!isset($data['result_id'])) {
            $this->messageManager->addErrorMessage(__('Result ID is required.'));
            return $resultJson;
        }
        $resultId = (int)$data['result_id'];
        $path = (string)($data['relative_path'] ?? '');
        $patchType = (int)($data['patch_type'] ?? PrManager::PATCH_TYPE_PATCH);
        try {
            $errors = $this->prManager->sendPrRequest($resultId, $path, $patchType);
        } catch (CouldNotSaveException|NoSuchEntityException|LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while creating the Pull Request.'));
            $errors = [
                'error' => $e->getMessage(),
            ];
        }
        $resultJson->setData($errors);
        return $resultJson;
    }
}
