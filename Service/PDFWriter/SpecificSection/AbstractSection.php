<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter;

abstract class AbstractSection
{
    public function __construct(
        public readonly \Crealoz\EasyAudit\Service\PDFWriter\SizeCalculation $sizeCalculation
    )
    {
    }


    /**
     * Write the section and manages the annex
     *
     * @param PDFWriter $pdfWriter
     * @param array $subresults
     * @param bool $isAnnex
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function writeSection(PDFWriter $pdfWriter, array $subresults, bool $isAnnex = false): void
    {
        if (!$isAnnex) {
            $pdfWriter->writeSubSectionIntro($subresults);
        }
        $this->writeContent($pdfWriter, $subresults);
    }

    /**
     * Write the content of the section
     *
     * @param PDFWriter $pdfWriter
     * @param array $subresults
     * @return void
     */
    abstract protected function writeContent(PDFWriter $pdfWriter, array $subresults): void;

    /**
     * Check if the page or the column has enough space for the next line
     *
     * @param PDFWriter $pdfWriter
     * @param $size
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    protected function manageColumnPage(PDFWriter $pdfWriter, $size): void
    {
        $size = (int) $size;
        if ($pdfWriter->y < $size) {
            $pdfWriter->switchColumnOrAddPage();
        }
    }
}