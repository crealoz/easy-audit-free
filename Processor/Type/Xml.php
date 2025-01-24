<?php

namespace Crealoz\EasyAudit\Processor\Type;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Xml extends AbstractType implements TypeInterface
{
    const ORDER = 30;

    /**
     * @inheritDoc
     */
    protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): bool
    {
        $hasErrors = false;
        foreach ($files as $xmlFile) {
            foreach ($processors as $processor) {
                if (!$processor instanceof AuditProcessorInterface) {
                    throw new \InvalidArgumentException('Processor must implement ProcessorInterface');
                }
                if ($processor instanceof FileProcessorInterface) {
                    $processor->setFile($xmlFile);
                }
                $processor->run();
                $progressBar?->advance();
                if ($hasErrors === false && $processor->hasErrors()) {
                    $hasErrors = true;
                }
            }
        }
        return $hasErrors;
    }

    protected function getProgressBarCount(array $processors, array $files): int
    {
        return count($processors) * count($files);
    }
}