<?php

namespace Crealoz\EasyAudit\Processor\Type;

use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;
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
            $progressBar?->advance();
            foreach ($processors as $processor) {
                if (!$processor instanceof ProcessorInterface) {
                    throw new \InvalidArgumentException('Processor must implement ProcessorInterface');
                }
                $processor->run($codeFile);
                if ($hasErrors === false && $processor->hasErrors()) {
                    $hasErrors = true;
                }
            }
        }
        return $hasErrors;
    }
}