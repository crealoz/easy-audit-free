<?php

namespace Crealoz\EasyAudit\Ui\Component\Form\Field;

use Crealoz\EasyAudit\Api\ResultRepositoryInterface;
use Crealoz\EasyAudit\Service\Config\MiddlewareHost;
use Crealoz\EasyAudit\Service\PrManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;

class PatchButton extends AbstractComponent
{
    public function __construct(
        ContextInterface $context,
        private readonly PrManager $prManager,
        private readonly ResultRepositoryInterface $resultRepository,
        private readonly MiddlewareHost $middlewareHost,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        $config = $this->getData('config');
        $requestParams = $this->context->getRequestParams();
        if (isset($requestParams['result_id'])) {
            $result = $this->resultRepository->getById($requestParams['result_id']);
            $processor = $result->getProcessor();
            $config['visible'] = $this->prManager->isPrEnabled($processor);
            $config['title'] = __('Generate Patch');
            if ($result->getDiff() !== null) {
                $config['title'] = __('Update Patch');
            }
            $config['title'] .= ' ('. __('A credit per file will be consumed') . ')';
            if (!$this->middlewareHost->isConfigured()) {
                $config['active'] = false;
                $config['title'] = __('Host is not configured. Please configure it using information https://shop.crealoz.fr/my-account/easyaudit/.');
            }
        }
        $this->setData('config', (array)$config);
        parent::prepare();
    }

    public function getComponentName()
    {
        return 'patchButton';
    }
}