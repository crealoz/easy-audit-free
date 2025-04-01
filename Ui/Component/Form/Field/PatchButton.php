<?php

namespace Crealoz\EasyAudit\Ui\Component\Form\Field;

use Crealoz\EasyAudit\Api\ResultRepositoryInterface;
use Crealoz\EasyAudit\Service\PrManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;

class PatchButton extends AbstractComponent
{
    public function __construct(
        ContextInterface $context,
        private readonly PrManager $prManager,
        private readonly ResultRepositoryInterface $resultRepository,
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
        }
        $this->setData('config', (array)$config);
        parent::prepare();
    }

    public function getComponentName()
    {
        return 'patchButton';
    }
}