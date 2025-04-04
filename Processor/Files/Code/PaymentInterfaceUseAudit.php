<?php

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractFileProcessor;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * This processor checks if the class extends \Magento\Payment\Model\Method\AbstractMethod and this way to create a paymement method is deprecated. Use the PaymentInterface instead.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 * @package Crealoz\EasyAudit\Processor\Files\Code
 */
class PaymentInterfaceUseAudit extends AbstractFileProcessor implements FileProcessorInterface
{
    public const ORDER = 60;

    public const TAG = 'paymentInterfaceUseAudit';

    public function __construct(
        AuditStorage $auditStorage,
        protected readonly DriverInterface $driver,
        protected readonly ModuleTools $moduleTools
    )
    {
        parent::__construct($auditStorage);
    }

    public function getProcessorName(): string
    {
        return __('Check the use of the PaymentInterface');
    }

    public function getAuditSection(): string
    {
        return __('PHP');
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'extensionOfAbstractMethod' => $this->getExtensionOfAbstractMethod(),
            ]
        ];
    }

    private function getExtensionOfAbstractMethod()
    {
        $title = 'Extension of abstract method';
        $description = 'The class extends \Magento\Payment\Model\Method\AbstractMethod and this way to create a paymement method is deprecated. Use the PaymentInterface instead.';
        return [
            'title' => $title,
            'description' => $description,
            'files' => []
        ];
    }

    public function run(): void
    {
        $moduleName = $this->moduleTools->getModuleNameByAnyFile($this->getFile());
        if ($this->auditStorage->isModuleIgnored($moduleName)) {
            return;
        }
        $code = $this->driver->fileGetContents($this->getFile());
        if (str_contains($code, 'extends \Magento\Payment\Model\Method\AbstractMethod')) {
            $this->addErroneousFile($this->getFile(), 1);
            $this->results['hasErrors'] = true;
            $this->results['errors']['extensionOfAbstractMethod']['files'][] = $this->getFile();
        }
    }
}
