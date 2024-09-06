<?php

namespace Crealoz\EasyAudit\Service\PDFWriter;

use Crealoz\EasyAudit\Service\PDFWriter;

class SpecificSections
{
    public function manageHelperInsteadOfViewModel(array $subresults, PDFWriter $pdfWriter): void
    {
        $pdfWriter->writeSubSectionIntro($subresults);
        $pdfWriter->writeLine('Files:');
        foreach ($subresults['files'] as $file => $usages) {
            if ($pdfWriter->y < 9 * 1.3 + $usages['usageCount'] * 12 + 10) {
                $pdfWriter->addPage();
            }
            $pdfWriter->writeLine('-' . $file . '(usages : ' . $usages['usageCount'] . ')');
            $pdfWriter->x += 5;
            unset($usages['usageCount']);
            foreach ($usages as $template => $usage) {
                $pdfWriter->setGeneralStyle(8);
                $pdfWriter->currentPage->drawText('-' . $template . '(' . $usage . ')', $pdfWriter->x, $pdfWriter->y);
                $pdfWriter->y -= 12;
                if ($pdfWriter->y < 50) {
                    $pdfWriter->addPage();
                }
            }
            $pdfWriter->x -= 5;
            $pdfWriter->y -= 10;
        }
    }

    public function manageBlockVMRatio(array $subresults, PDFWriter $pdfWriter): void
    {
        $pdfWriter->writeSubSectionIntro($subresults);
        $pdfWriter->writeLine('Modules:');
        foreach ($subresults['files'] as $module => $ratio) {
            $pdfWriter->writeLine('-' . $module . '(ratio : ' . $ratio . ')');
        }
    }

    public function manageUnusedModules(array $subresults, PDFWriter $pdfWriter): void
    {
        $pdfWriter->writeSubSectionIntro($subresults);
        $pdfWriter->writeLine('Modules:');
        foreach ($subresults['files'] as $module) {
            $pdfWriter->writeLine('-' . $module);
        }
    }

    public function irretrievableConfig(array $subresults, PDFWriter $pdfWriter): void
    {
        $pdfWriter->writeSubSectionIntro($subresults);
        $pdfWriter->writeLine(__('%1 modules configuration cannot be retrieved', count($subresults['files'])));
    }
}