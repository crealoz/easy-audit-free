<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Processor\Results\ResultProcessorInterface;
use Crealoz\EasyAudit\Processor\Type\TypeFactory;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;
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
        protected readonly LoggerInterface $logger,
        protected array                $processors = [],
        protected array                $resultProcessors = []
    )
    {

    }

    /**
     * @param OutputInterface|null $output
     * @param string|null $language
     * @param string $filename
     * @return string
     * @throws FileSystemException
     */
    public function run(OutputInterface $output = null, string $language = null, string $filename = "audit"): string
    {
        $this->results = [];
        // if the filename is not valid unix filename, throw an exception
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $filename)) {
            throw new FileSystemException(__('Invalid filename %1', $filename));
        }
        $erroneousFiles = [];
        $this->logger->debug(__('Starting audit service...'));
        $this->initializeProcessorsResults();
        foreach ($this->processors as $typeName => $subTypes) {
            $type = $this->typeFactory->get($typeName);
            $this->results = array_merge_recursive($type->process($subTypes, $typeName, $output), $this->results);
            $erroneousFiles[$typeName] = $type->getErroneousFiles();
        }

        $this->logger->debug(__('Audit service has been run successfully.'));
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
        try {
            return $this->pdfWriter->createdPDF($this->results, $language, $filename);
        } catch (FileSystemException $e) {
            $this->logger->error(__('Error while creating or reading the PDF file: %1', $e->getMessage()));
        } catch (\Zend_Pdf_Exception $e) {
            $this->logger->error(__('Error while generating the PDF definition: %1', $e->getMessage()));
        }
    }

    private function initializeProcessorsResults(): void
    {
        foreach ($this->processors as $typeName => $subTypes) {
            $type = $this->typeFactory->create($typeName);
            $type->initResults($subTypes);
        }
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
