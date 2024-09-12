<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\Type\TypeFactory;
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
     * @throws \Zend_Pdf_Exception
     * @throws FileSystemException
     */
    public function run(OutputInterface $output = null, string $language = null): string
    {
        $erroneousFiles = [];
        foreach ($this->processors as $typeName => $subTypes) {
            $type = $this->typeFactory->create($typeName);
            $this->results[$typeName] = $type->process($subTypes, $typeName, $output);
            $erroneousFiles[$typeName] = $type->getErroneousFiles();
        }
        $consolidatedErroneousFiles = [];
        foreach ($erroneousFiles as $files) {
            foreach ($files as $file => $score) {
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
            $processor->process($this->results);
        }
        if ($output instanceof OutputInterface) {
            $output->writeln(PHP_EOL . 'Creating PDF...');
        }
        return $this->pdfWriter->createdPDF($this->results, $language);
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
