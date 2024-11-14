<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\PDFWriter;

class UnusedModules implements SectionInterface
{
    public function __construct(
        public readonly PDFWriter\SizeCalculation $sizeCalculation
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function writeSection(PDFWriter $pdfWriter, array $subresults, bool $isAnnex = false): void
    {
        if (!$isAnnex) {
            $pdfWriter->writeSubSectionIntro($subresults);
        }
        $pdfWriter->writeLine('Modules:');
        foreach ($subresults['files'] as $module) {
            if ($pdfWriter->y < 9 * 1.3) {
                $pdfWriter->switchColumnOrAddPage();
            }
            $pdfWriter->writeLine('-' . $module);
        }
    }

    /**
     * @inheritDoc
     */
    public function calculateSize(array $subresults): int
    {
        $size = $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize([$subresults]);
        $size += $this->sizeCalculation->getSizeForText(__('Modules:'));
        foreach ($subresults['files'] as $module) {
            $size += $this->sizeCalculation->getSizeForText('-' . $module);
        }
        return $size;
    }
}