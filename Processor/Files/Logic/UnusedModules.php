<?php

namespace Crealoz\EasyAudit\Processor\Files\Logic;

use Crealoz\EasyAudit\Api\Processor\Audit\ArrayProcessorInterface;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractArrayProcessor;
use Crealoz\EasyAudit\Processor\Files\Logic\Modules\GetModuleConfig;
use Magento\Framework\Exception\FileSystemException;

class UnusedModules extends AbstractArrayProcessor implements ArrayProcessorInterface
{
    /**
     * @readonly
     */
    private GetModuleConfig $getModuleConfig;
    public function getAuditSection(): string
    {
        return __('Logic');
    }

    public function getProcessorName(): string
    {
        return __('Unused Modules');
    }

    public function __construct(
        AuditStorage $auditStorage,
        GetModuleConfig $getModuleConfig
    )
    {
        $this->getModuleConfig = $getModuleConfig;
        parent::__construct($auditStorage);
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [
                'unusedModules' => $this->getUnusedModulesEntry()
            ]
        ];
    }

    private function getUnusedModulesEntry(): array
    {
        $title = __('Unused Modules');
        $explanation = __('The following modules are not active. Consider removing them.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'manageUnusedModules'
        ];
    }

    /**
     * @throws FileSystemException
     */
    public function run(): void
    {
        $unusedModules = $this->getModuleConfig->process($this->getArray());
        if (!empty($unusedModules)) {
            foreach ($unusedModules as $module) {
                $this->results['hasErrors'] = true;
                $this->results['suggestions']['unusedModules']['files'][] = $module;
            }
        }
    }

    public function getProcessorTag(): string
    {
        return 'unusedModules';
    }
}