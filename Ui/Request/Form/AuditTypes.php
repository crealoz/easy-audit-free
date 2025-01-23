<?php

namespace Crealoz\EasyAudit\Ui\Request\Form;

use Crealoz\EasyAudit\Service\Audit;
use Psr\Log\LoggerInterface;

class AuditTypes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @readonly
     */
    private Audit $auditService;
    public function __construct(Audit $auditService)
    {
        $this->auditService = $auditService;
    }

    public function toOptionArray()
    {
        $options = [];
        $processors = $this->auditService->getAvailableProcessors();
        foreach ($this->recursivelyGetProcessor($processors) as $processorName => $processorPath) {
            $options[] = [
                'label' => $processorName,
                'value' => $processorPath
            ];
        }
        return $options;
    }

    protected function recursivelyGetProcessor(array $processors, string $path = ''): array
    {
        $result = [];
        foreach ($processors as $processorName => $subProcessors) {
            $currentPath = $path ? $path . '/' . $processorName : $processorName;
            if (is_array($subProcessors)) {
                $result = array_merge($result, $this->recursivelyGetProcessor($subProcessors, $currentPath));
            } else {
                $result[$processorName] = $currentPath . ':' . get_class($subProcessors);
            }
        }
        return $result;
    }
}