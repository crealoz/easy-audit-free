<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter;

class SpecificModel implements SectionInterface
{

    public function writeSection(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeSubSectionIntro($subresults);
        $pdfWriter->writeLine('Files:');
        foreach ($subresults['files'] as $file => $arguments) {
            $pdfWriter->writeLine($file);
            foreach ($arguments as $argument) {
                $pdfWriter->writeLine('-' . $argument , 6);
            }
        }
    }
}