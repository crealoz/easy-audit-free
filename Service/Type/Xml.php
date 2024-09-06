<?php

namespace Crealoz\EasyAudit\Service\Type;

use Crealoz\EasyAudit\Service\Processor\ProcessorInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Xml extends AbstractType implements TypeInterface
{

    protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): void
    {
        foreach ($files as $xmlFile) {
            $xml = simplexml_load_file($xmlFile);
            $progressBar?->advance();
            if ($xml === false) {
                $this->logger->error("Failed to load XML file: $xmlFile");
                continue;
            }
            foreach ($processors as $processor) {
                if (!$processor instanceof ProcessorInterface) {
                    throw new \InvalidArgumentException('Processor must implement ProcessorInterface');
                }
                $processor->run($xml);
            }
        }
    }
}