<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter;

class SpecificClass implements SectionInterface
{

    public function __construct(
        public readonly PDFWriter\SizeCalculation $sizeCalculation,
        private readonly ModulePaths $modulePaths
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
            if ($pdfWriter->y < 9 * 1.3) {
                $pdfWriter->switchColumnOrAddPage();
            }
            $pdfWriter->writeLine('-' . $this->modulePaths->stripVendorOrApp($file) . '(potential issues count : ' . count($arguments) . ')');
        }
    }

    /**
     * @inheritDoc
     */
    public function calculateSize(array $subresults): int
    {
        $size = $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize([$subresults]);
        $size += $this->sizeCalculation->getSizeForText('Files:');
        foreach ($subresults['files'] as $file => $arguments) {
            $size += $this->sizeCalculation->getSizeForText('-' . $file . '(potential issues count : ' . count($arguments) . ')');
        }
        return $size;
    }
}