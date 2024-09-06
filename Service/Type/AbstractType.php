<?php

namespace Crealoz\EasyAudit\Service\Type;

use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractType implements TypeInterface
{
    protected array $results = [];

    protected array $fileGetters = [];

    public function __construct(
        protected readonly FileGetterFactory $fileGetterFactory,
        protected readonly LoggerInterface $logger
    ) {

    }

    public function process(array $subTypes, string $type, OutputInterface $output = null): array
    {
        foreach ($subTypes as $subType => $processors) {
            $fileGetter = $this->getFileGetter($subType);
            $files = $fileGetter->execute();
            if (!empty($files)) {
                $progressBar = null;
                if ($output) {
                    $output->writeln("\r\nProcessing $subType files...");
                    /** if we are in command line, we display a bar */
                    $progressBar = new ProgressBar($output, count($files));
                    $progressBar->start();
                }
                $this->doProcess($processors, $files, $progressBar);
                foreach ($processors as $processor) {
                    $this->results[$processor->getAuditSection()][$processor->getProcessorName()] = $processor->getResults();
                }
                if ($output) {
                    $progressBar->finish();
                }
            }
        }
        return $this->results;
    }

    abstract protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): void;

    protected function getFileGetter(string $type): FileGetterInterface
    {
        if (!isset($this->fileGetters[$type])) {
            $this->fileGetters[$type] = $this->fileGetterFactory->create($type);
        }
        return $this->fileGetters[$type];
    }

}