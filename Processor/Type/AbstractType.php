<?php

namespace Crealoz\EasyAudit\Processor\Type;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractType implements TypeInterface
{
    const ORDER = 0;
    protected array $results = [];

    protected array $fileGetters = [];

    protected array $erroneousFiles = [];

    protected bool $hasErrors = false;

    public function __construct(
        protected readonly FileGetterFactory $fileGetterFactory,
        protected readonly LoggerInterface $logger
    ) {
    }

    public function getOrder(): int
    {
        return static::ORDER;
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    /**
     * Processes the files of the given subtypes against the given processors. If any of the processor is erroneous,
     * the hasErrors flag is set to true.
     *
     * @param array $subTypes
     * @param string $type
     * @param OutputInterface|null $output
     * @return array
     */
    public function process(array $subTypes, string $type, OutputInterface $output = null): array
    {
        $this->results = [];
        $this->erroneousFiles = [];
        foreach ($subTypes as $subType => $processors) {
            $fileGetter = $this->getFileGetter($subType);
            $files = $fileGetter->execute();
            if ($files !== []) {
                $progressBar = null;
                if ($output instanceof \Symfony\Component\Console\Output\OutputInterface) {
                    $output->writeln("\r\nProcessing $subType files...");
                    /** if we are in command line, we display a bar */
                    $progressBar = new ProgressBar($output, $this->getProgressBarCount($processors, $files));
                    $progressBar->start();
                }
                $errors = $this->doProcess($processors, $files, $progressBar);
                if ($this->hasErrors === false && $errors) {
                    $this->hasErrors = true;
                }
                $this->manageResults($processors);
                if ($output instanceof \Symfony\Component\Console\Output\OutputInterface) {
                    $progressBar->finish();
                }
            }
        }
        return $this->results;
    }

    abstract protected function getProgressBarCount(array $processors, array $files): int;

    /**
     * Initializes the results array to avoid malformed results.
     *
     * @param array $subTypes
     * @return void
     */
    public function initResults(array $subTypes): void
    {
        foreach ($subTypes as $processors) {
            foreach ($processors as $processor) {
                $processor->prepopulateResults();
            }
        }
    }

    /**
     * Processes the files against the processors.
     *
     * @param array $processors
     * @param array $files
     * @param ProgressBar|null $progressBar
     * @return bool
     */
    abstract protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): bool;

    /**
     * Returns the file getter for the given type. If the file getter is not already created, it will be created.
     *
     * @param string $type
     * @return FileGetterInterface
     */
    protected function getFileGetter(string $type): FileGetterInterface
    {
        if (!isset($this->fileGetters[$type])) {
            $this->fileGetters[$type] = $this->fileGetterFactory->create($type);
        }
        return $this->fileGetters[$type];
    }

    /**
     * Consolidate the results of the processors and the erroneous files. It will set a score for each erroneous file
     * depending on the number and severity of errors found.
     *
     * @param array $processors
     * @return void
     */
    protected function manageResults(array $processors) : void
    {
        /** @var AuditProcessorInterface $processor */
        foreach ($processors as $processor) {
            $results = $processor->getResults();
            $results['processorTag'] = $processor->getProcessorTag();
            $results['title'] = $processor->getProcessorName();
            $this->results[$processor->getAuditSection()][$processor->getOrder()] = $results;
            foreach ($processor->getErroneousFiles() as $file => $score) {
                if (isset($this->erroneousFiles[$file])) {
                    $this->erroneousFiles[$file] += $score;
                } else {
                    $this->erroneousFiles[$file] = $score;
                }
            }
        }
    }

    /**
     * Returns the results of the processors.
     *
     * @return array
     */
    public function getErroneousFiles(): array
    {
        return $this->erroneousFiles;
    }

}