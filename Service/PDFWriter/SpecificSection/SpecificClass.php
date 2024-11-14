<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\PDFWriter;

class SpecificClass implements SectionInterface
{

    public function writeSection(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeSubSectionIntro($subresults);
        $pdfWriter->writeLine('Files:');
        foreach ($subresults['files'] as $file => $arguments) {
            $pdfWriter->writeLine('-' . $file . '(potential issues count : ' . count($arguments) . ')');
        }
    }
}