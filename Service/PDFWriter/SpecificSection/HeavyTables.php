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

use Crealoz\EasyAudit\Service\PDFWriter;

class HeavyTables extends PDFWriter\SpecificSection\AbstractSection implements \Crealoz\EasyAudit\Api\Result\SectionInterface
{

    /**
     * @inheritDoc
     */
    protected function writeContent(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeLine(__('Tables:'));
        foreach ($subresults as $tableName => $size) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3);
            $pdfWriter->writeLine($this->getLine($tableName, $size));
        }
    }

    /**
     * @inheritDoc
     */
    public function calculateSize(array $subresults): int
    {
        $size = $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize([$subresults]);
        $size += $this->sizeCalculation->getSizeForText(__('Tables:'));
        $pages = $this->sizeCalculation->getNumberOfPagesForFiles($subresults['files']);
        $columnCount = $pages > 1 ? 2 : 1;
        foreach ($subresults as $tableName => $tableSize) {
            $size += $this->sizeCalculation->getSizeForText($this->getLine($tableName, $tableSize), $columnCount);
        }
        return $size;
    }

    public function getLine(string $key, mixed $entry): string
    {
        return '-' . $key . '('.__('size') . ' : ' . $entry . ')';
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = __('Tables') . PHP_EOL;
        foreach ($subResults as $tableName => $size) {
            $text .= $this->getLine($tableName, $size) . PHP_EOL;
        }
        return $text;
    }
}
