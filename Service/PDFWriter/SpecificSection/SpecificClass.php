<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter;

class SpecificClass implements SectionInterface
{

    public function writeSection(PDFWriter $pdfWriter, array $subresults): void
    {
        if (count($subresults['files']) >= 1) {
            $pdfWriter->writeSubSectionIntro($subresults);
            $pdfWriter->writeLine('Several files inject object without using interface, builder or factory.');
        }
    }
}