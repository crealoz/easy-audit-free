<?php
/**
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter;

class AdvancedBlockVsVM extends PDFWriter\SpecificSection\AbstractSection implements \Crealoz\EasyAudit\Api\Result\SectionInterface
{
    public function __construct(
        PDFWriter\SizeCalculation $sizeCalculation,
        private readonly ModulePaths $modulePaths
    )
    {
        parent::__construct($sizeCalculation);
    }

    /**
     * @inheritDoc
     */
    protected function writeContent(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeLine('Files:');
        foreach ($subresults['files'] as $file => $phtmlFiles) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3 + count($phtmlFiles) * 12 + 10);
            $pdfWriter->writeLine($this->modulePaths->stripVendorOrApp($file), true);
            foreach ($phtmlFiles as $phtmlFile => $occurrences) {
                $pdfWriter->writeLine($this->getOccurrenceText($phtmlFile, $occurrences), true, 8);
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

    public function getLine(string $key, mixed $entry): string
    {
        $text = __('Files');
        foreach ($entry as $file => $phtmlFiles) {
            $text .= $this->modulePaths->stripVendorOrApp($file);
            foreach ($phtmlFiles as $phtmlFile => $occurrences) {
                $text .= $this->getOccurrenceText($phtmlFile, $occurrences);
            }
        }
        return $text;
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = __('Files') . PHP_EOL;
        foreach ($subResults as $file => $phtmlFiles) {
            $text .= $this->modulePaths->stripVendorOrApp($file) . PHP_EOL;
            foreach ($phtmlFiles as $phtmlFile => $occurrences) {
                $text .= $this->getOccurrenceText($phtmlFile, $occurrences) . PHP_EOL;
            }
        }
        return $text;
    }

    private function getOccurrenceText(string $phtmlFile, int $occurrences): string
    {
        return __('-%1 (%2 occurrences)', $this->modulePaths->stripVendorOrApp($phtmlFile), $occurrences);
    }
}
