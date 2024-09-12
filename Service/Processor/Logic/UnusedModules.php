<?php

namespace Crealoz\EasyAudit\Service\Processor\Logic;

use Crealoz\EasyAudit\Service\Processor\AbstractProcessor;
use Crealoz\EasyAudit\Service\Processor\Logic\Modules\GetModuleConfig;
use Crealoz\EasyAudit\Service\Processor\ProcessorInterface;
use Magento\Framework\Exception\FileSystemException;

class UnusedModules extends AbstractProcessor implements ProcessorInterface
{
    protected string $processorName = 'Unused Modules';

    protected array $results = [
        'hasErrors' => false,
        'errors' => [],
        'warnings' => [],
        'suggestions' => [
            'unusedModules' => [
                'title' => 'Unused Modules',
                'explanation' => 'The following modules are not active. Consider removing them.',
                'files' => [],
                'specificSections' => 'manageUnusedModules'
            ],
        ]
    ];

    protected string $auditSection = 'Logic';

    public function __construct(
        private readonly GetModuleConfig $getModuleConfig
    )
    {
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