<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter;

class BlockVMRatio implements SectionInterface
{
    public function writeSection(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeSubSectionIntro($subresults);
        $pdfWriter->writeLine('Modules:');
        foreach ($subresults['files'] as $module => $ratio) {
            $pdfWriter->writeLine('-' . $module . '(ratio : ' . $ratio . ')');
        }
    }
}