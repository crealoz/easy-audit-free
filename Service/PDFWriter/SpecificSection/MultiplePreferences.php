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

class MultiplePreferences extends PDFWriter\SpecificSection\AbstractSection implements \Crealoz\EasyAudit\Api\Result\SectionInterface
{
    public function __construct(
        PDFWriter\SizeCalculation $sizeCalculation,
        private readonly ModulePaths $modulePaths,
        private readonly PDFWriter\StyleManager $styleManager
    )
    {
        parent::__construct($sizeCalculation);
    }

    /**
     * @inheritDoc
     */
    protected function writeContent(PDFWriter $pdfWriter, array $subresults, bool $isAnnex = false): void
    {
        $pdfWriter->writeLine('Preferences:');
        foreach ($subresults['files'] as $preference => $usages) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3 + count($usages) * 12 + 10);
            $pdfWriter->writeLine($this->getPreferenceLine($preference, $usages));
            $pdfWriter->x += 5;
            foreach ($usages as $file) {
                $this->styleManager->setGeneralStyle($pdfWriter->currentPage, 8);
                $pdfWriter->currentPage->drawText('-' . $this->modulePaths->stripVendorOrApp($file), $pdfWriter->x, $pdfWriter->y);
                $pdfWriter->y -= 12;
                if ($pdfWriter->y < 50) {
                    $pdfWriter->addPage();
                }
            }
            $pdfWriter->x -= 5;
            $pdfWriter->y -= 10;
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
        $text = __('Preferences');
        foreach ($entry as $preference => $usages) {
            $text .= $this->getPreferenceLine($preference, $usages);
            foreach ($usages as $file) {
                $text .= $this->modulePaths->stripVendorOrApp($file);
            }
        }
        return $text;
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = __('Preferences') . PHP_EOL;
        foreach ($subResults as $preference => $usages) {
            $text .= $this->getPreferenceLine($preference, $usages) . PHP_EOL;
            foreach ($usages as $file) {
                $text .= $this->modulePaths->stripVendorOrApp($file) . PHP_EOL;
            }
        }
        return $text;
    }

    private function getPreferenceLine(string $preference, array $usages): string
    {
        return $preference . '(' . __('usages') . ' : ' . count($usages) . ')';
    }
}
