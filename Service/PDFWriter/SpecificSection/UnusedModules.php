<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\PDFWriter;

class UnusedModules implements SectionInterface
{
    public function writeSection(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeSubSectionIntro($subresults);
        $pdfWriter->writeLine('Modules:');
        foreach ($subresults['files'] as $module) {
            $pdfWriter->writeLine('-' . $module);
        }
    }
}