<?php

namespace Crealoz\EasyAudit\Processor\Type;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class PHPCode extends AbstractType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): bool
    {
        $hasErrors = false;
        foreach ($files as $codeFile) {
            // ignores autoload.php and registration.php
            if (str_contains($codeFile, 'autoload.php') || str_contains($codeFile, 'registration.php')) {
                continue;
            }
            foreach ($processors as $processor) {
                if (!$processor instanceof AuditProcessorInterface) {
                    throw new \InvalidArgumentException('Processor must implement ProcessorInterface');
                }
                if ($processor instanceof FileProcessorInterface) {
                    $processor->setFile($codeFile);
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