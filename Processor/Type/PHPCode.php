<?php

namespace Crealoz\EasyAudit\Processor\Type;

use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class PHPCode extends AbstractType implements TypeInterface
{


    protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): void
    {
        foreach ($files as $codeFile) {
            $progressBar?->advance();
            foreach ($processors as $processor) {
                if (!$processor instanceof ProcessorInterface) {
                    throw new \InvalidArgumentException('Processor must implement ProcessorInterface');
                }
                $processor->run($codeFile);
            }
        }
    }
}