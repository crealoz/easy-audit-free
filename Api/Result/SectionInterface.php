<?php

namespace Crealoz\EasyAudit\Api\Result;

use Crealoz\EasyAudit\Service\PDFWriter;

interface SectionInterface
{
    /**
     * @param PDFWriter $pdfWriter
     * @param array $subresults
     * @param bool $isAnnex
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function writeSection(PDFWriter $pdfWriter, array $subresults, bool $isAnnex = false): void;

    /**
     * Calculate the size of the section
     *
     * @param array $subresults
     * @return int
     */
    public function calculateSize(array $subresults): int;

    /**
     * Get the line
     *
     * @param string $key
     * @param mixed $entry
     * @return string
     */
    public function getLine(string $key, $entry): string;

    /**
     * Get the PHP formated text
     *
     * @param string $key
     * @param array $subResults
     * @return string
     */
    public function getPHPFormatedText(string $key, array $subResults): string;

}