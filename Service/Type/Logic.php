<?php

namespace Crealoz\EasyAudit\Service\Type;

use Crealoz\EasyAudit\Service\Processor\ProcessorInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Logic extends AbstractType implements TypeInterface
{

    protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): void
    {
        foreach ($processors as $processor) {
            $progressBar?->advance();
            if (!$processor instanceof ProcessorInterface) {
                throw new \InvalidArgumentException('Processor must implement ProcessorInterface');
            }
            $processor->run($files);
        }
    }
}