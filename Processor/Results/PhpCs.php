<?php


namespace Crealoz\EasyAudit\Processor\Results;

use Crealoz\EasyAudit\Service\PDFWriter;
use Crealoz\EasyAudit\Processor\Files\Code\PhpCs as PhpCsFileProcessor;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

class PhpCs implements \Crealoz\EasyAudit\Api\Processor\ResultProcessorInterface
{

    public function __construct(
        private readonly PDFWriter $pdfWriter,
        private readonly LoggerInterface $logger
    )
    {

    }

    public function processResults(array $results): array
    {
        unset($results['introduction']['overall']['summary']['EasyAudit']);
        /** Write a specific PDF for phpcs results */
        if (!isset($results['PHP'][PhpCsFileProcessor::ORDER])) {
            return $results;
        }
        $phpCsResults = $results['PHP'][PhpCsFileProcessor::ORDER];
        if (empty($phpCsResults)) {
            return $results;
        }
        try {
            $phpCsFile = $this->pdfWriter->createdPDF(['phpCS' => [$phpCsResults]], 'phpCs');
            $results['introduction']['overall']['summary']['phpcs'] = __('PHP Code Sniffer Results were generated in a different file. Check the zip file for more information.');
            unset($results['PHP'][PhpCsFileProcessor::ORDER]);
            $results['data']['files'][] = ['filename' => $phpCsFile, 'request_id' => $results['data']['requestId'], 'content' => __('File that contains the PHP Code Sniffer results')];
        } catch (FileSystemException|\Zend_Pdf_Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $results;
    }
}
