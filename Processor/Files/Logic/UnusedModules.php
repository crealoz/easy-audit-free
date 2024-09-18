<?php

namespace Crealoz\EasyAudit\Processor\Files\Logic;

use Crealoz\EasyAudit\Processor\Files\AbstractProcessor;
use Crealoz\EasyAudit\Processor\Files\Logic\Modules\GetModuleConfig;
use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;
use Magento\Framework\Exception\FileSystemException;

class UnusedModules extends AbstractProcessor implements ProcessorInterface
{
    public function getAuditSection(): string
    {
        return __('Logic');
    }

    public function getProcessorName(): string
    {
        return __('Unused Modules');
    }

    public function __construct(
        private readonly GetModuleConfig $getModuleConfig
    )
    {
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
    public function run($input)
    {
        $unusedModules = $this->getModuleConfig->process($input);
        if (!empty($unusedModules)) {
            foreach ($unusedModules as $module) {
                $this->results['hasErrors'] = true;
                $this->results['suggestions']['unusedModules']['files'][] = $module;
            }
        }
    }
}