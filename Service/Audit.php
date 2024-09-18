<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Processor\Results\ResultProcessorInterface;
use Crealoz\EasyAudit\Processor\Type\TypeFactory;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Output\OutputInterface;

class Audit
{
    const PRIORITY_HIGH = 3;

    const PRIORITY_AVERAGE = 2;

    const PRIORITY_LOW = 1;

    protected array $results = [];

    public function __construct(
        protected readonly PDFWriter   $pdfWriter,
        protected readonly TypeFactory $typeFactory,
        private readonly ArrayTools    $arrayTools,
        protected array                $processors = [],
        protected array                $resultProcessors = []
    )
    {

    }

    /**
     * @throws FileSystemException
     */
    public function run(OutputInterface $output = null, string $language = null, $filename = "audit"): string
    {
        $this->results = [];
        // if the filename is not valid unix filename, throw an exception
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $filename)) {
            throw new FileSystemException(__('Invalid filename %1', $filename));
        }
        $erroneousFiles = [];
        foreach ($this->processors as $typeName => $subTypes) {
            $type = $this->typeFactory->create($typeName);
            $this->results[$typeName] = $type->process($subTypes, $typeName, $output);
            $erroneousFiles[$typeName] = $type->getErroneousFiles();
        }
        $consolidatedErroneousFiles = [];
        foreach ($erroneousFiles as $files) {
            foreach ($files as $file => $score) {
                // if file consists only in empty spaces and line feed, we skip it
                if (trim($file) === '') {
                    continue;
                }

                if (isset($consolidatedErroneousFiles[$file])) {
                    $consolidatedErroneousFiles[$file] += $score;
                } else {
                    $consolidatedErroneousFiles[$file] = $score;
                }
            }
        }
        arsort($consolidatedErroneousFiles);
        $this->results['erroneousFiles'] = $consolidatedErroneousFiles;
        if ($output instanceof OutputInterface) {
            $output->writeln(PHP_EOL . 'Processing results...');
        }
        foreach ($this->resultProcessors as $processor) {
            if ($processor instanceof ResultProcessorInterface) {
                $this->results = $processor->processResults($this->results);
            }
        }
        if ($output instanceof OutputInterface) {
            $output->writeln(PHP_EOL . 'Creating PDF...');
        }
        return $this->pdfWriter->createdPDF($this->results, $language, $filename);
    }

    public function getAvailableProcessors(): array
    {
        return $this->processors;
    }

    /**
     * Maps the requested processors to the available ones
     * @param array $processors
     * @return void
     */
    public function setProcessors(array $processors): void
    {
        $availableProcessors = $this->getAvailableProcessors();
        $requiredProcessors = $this->arrayTools->recursiveArrayIntersect($availableProcessors, $processors);
        $this->processors = $requiredProcessors;
    }
}
