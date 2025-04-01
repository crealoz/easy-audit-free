<?php
/**
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */

namespace Crealoz\EasyAudit\Processor\Type;

use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Processor\Type\AbstractType;
use Crealoz\EasyAudit\Processor\Type\TypeInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Database extends AbstractType implements TypeInterface
{
    const ORDER = 40;

    /**
     * @inheritDoc
     */
    public function process(array $subTypes, string $type, OutputInterface $output = null): array
    {
        $processorCount = count($subTypes, COUNT_RECURSIVE);
        $progressBar = null;
        if ($output instanceof \Symfony\Component\Console\Output\OutputInterface) {
            $output->writeln(PHP_EOL . "Processing database checks");
            /** if we are in command line, we display a bar */
            $progressBar = new ProgressBar($output, $processorCount);
            $progressBar->start();
        }
        $this->results = [];
        $firstKey = array_key_first($subTypes);
        $this->hasErrors = $this->doProcess($subTypes[$firstKey], [], $progressBar);
        if ($output instanceof \Symfony\Component\Console\Output\OutputInterface) {
            $progressBar->finish();
        }
        return $this->results;
    }

    /**
     * @inheritDoc
     */
    public function getErroneousFiles(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): bool
    {
        $hasErrors = false;
        foreach ($processors as $processor) {
            if (!$processor instanceof AuditProcessorInterface) {
                throw new \InvalidArgumentException('Processor must implement ProcessorInterface');
            }
            $processor->run();
            if ($hasErrors === false && $processor->hasErrors()) {
                $hasErrors = true;
            }
            $progressBar?->advance();
        }
        return $hasErrors;
    }

    protected function getProgressBarCount(array $processors, array $files): int
    {
        return count($processors);
    }
}
