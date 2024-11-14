<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\PDFWriter;

class SpecificModel implements SectionInterface
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
        $pdfWriter->writeLine('Files:');
        foreach ($subresults['files'] as $file => $arguments) {
            if ($pdfWriter->y < 9 * 1.3 + count($arguments) * 12 + 10) {
                $pdfWriter->switchColumnOrAddPage();
            }
            $pdfWriter->writeLine($pdfWriter->stripVendorOrApp($file), true);
            foreach ($arguments as $argument) {
                $pdfWriter->writeLine('-' . $argument , true, 8);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function calculateSize(array $subresults): int
    {
        $size = $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize([$subresults]);
        $size += $this->sizeCalculation->getSizeForText('Files:');
        $size += $this->sizeCalculation->calculateMultidimensionalArraySize($subresults['files']);
        return $size;
    }
}