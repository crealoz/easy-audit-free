<?php

namespace Crealoz\EasyAudit\Processor\Type;

use Crealoz\EasyAudit\Api\Processor\Audit\ArrayProcessorInterface;
use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Logic extends AbstractType implements TypeInterface
{

    /**
     * @inheritDoc
     */
    protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): bool
    {
        $hasErrors = false;
        foreach ($processors as $processor) {
            ($nullsafeVariable1 = $progressBar) ? $nullsafeVariable1->advance() : null;
            if (!$processor instanceof AuditProcessorInterface) {
                throw new \InvalidArgumentException('Processor must implement AuditProcessorInterface');
            }
            if ($processor instanceof ArrayProcessorInterface) {
                $processor->setArray($files);
            }
            $processor->run();
            if ($hasErrors === false && $processor->hasErrors()) {
                $hasErrors = true;
            }
        }
        return $hasErrors;
    }

    protected function getProgressBarCount(array $processors, array $files): int
    {
        return count($processors);
    }
}