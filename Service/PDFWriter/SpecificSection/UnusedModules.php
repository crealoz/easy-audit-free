<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\PDFWriter;

class UnusedModules extends AbstractSection implements SectionInterface
{

    /**
     * @inheritDoc
     */
    protected function writeContent(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeLine(__('Modules:'));
        foreach ($subresults['files'] as $module) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3);
            $pdfWriter->writeLine($this->getLine('', $module));
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
            $size += $this->sizeCalculation->getSizeForText($this->getLine('', $module));
        }
        return $size;
    }

    /**
     * @param mixed $entry
     */
    public function getLine($key, $entry): string
    {
        unset($key);
        return '-' . $entry;
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = __('Modules') . PHP_EOL;
        foreach ($subResults as $module) {
            $text .= '-' . $module . PHP_EOL;
        }
        return $text;
    }
}