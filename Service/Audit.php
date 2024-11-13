<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Api\Processor\ResultProcessorInterface;
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
        $hasErrors = false;
        foreach ($this->processors as $typeName => $subTypes) {
            $type = $this->typeFactory->get($typeName);
            $typeResults = $type->process($subTypes, $typeName, $output);
            if ($type->hasErrors()) {
                $hasErrors = true;
                $this->results = array_merge_recursive($typeResults, $this->results);
                $erroneousFiles[$typeName] = $type->getErroneousFiles();
            }
        }

        $this->logger->debug(__('Audit service has been run successfully.'));
        $this->results['introduction']['overall']['summary'] = $this->getOverAll();
        if (!$hasErrors) {
            $this->results['introduction']['overall']['summary'][] = __('Congratulations! No errors found.');
        }

        $this->results['erroneousFiles'] = $this->consolidateResults($erroneousFiles);
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

    private function consolidateResults($erroneousFiles): array
    {
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
        return $consolidatedErroneousFiles;
    }

    private function getOverAll(): array
    {
        return [
            'disclaimer' => $this->getDisclaimer(),
            'easyAuditPremium' => $this->getEasyAuditPremium(),
            'selfBranding' => $this->getSelfBranding(),
        ];
    }

    private function getSelfBranding(): string
    {
        return __('Crealoz can help you to understand or to deepen the analysis of your codebase. We can provide you with a professional audit of your Magento 2 codebase. You can find more information on our website: https://www.crealoz.fr/un-projet/');
    }

    private function getEasyAuditPremium(): string
    {
        return __('If you want to go further, you can use EasyAudit Premium, a paid version of EasyAudit that provides more features and more in-depth analysis. You can find more information on our website: https://www.crealoz.fr/crealoz-easy-audit/');
    }

    private function getDisclaimer(): string
    {
        return __('This audit has been generated by EasyAudit, a tool developed by Crealoz. It is a free tool that helps you to audit your Magento 2 codebase. It is not a replacement for a professional audit. It is a tool that helps you to identify potential issues in your codebase. It is recommended to use this tool in conjunction with a professional audit.');
    }
}
