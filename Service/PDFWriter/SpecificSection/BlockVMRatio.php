<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\PDFWriter;

class BlockVMRatio extends AbstractSection implements SectionInterface
{
    protected function writeContent(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeLine(__('Modules:'));
        foreach ($subresults['files'] as $module => $ratio) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3);
            $pdfWriter->writeLine($this->getLine($module, $ratio));
        }
    }

    /**
     * @inheritDoc
     */
    public function calculateSize(array $subresults): int
    {
        $size = $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize([$subresults]);
        $size += $this->sizeCalculation->getSizeForText(__('Modules:'));
        foreach ($subresults['files'] as $module => $ratio) {
            $size += $this->sizeCalculation->getSizeForText($this->getLine($module, $ratio));
        }
        return $size;
    }

    /**
     * @param mixed $entry
     */
    public function getLine($key, $entry): string
    {
        return __('-%1(ratio : %2)', $key, $entry);
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = __('Modules') . PHP_EOL;
        foreach ($subResults as $module => $ratio) {
            $text .= __('-%1(ratio : %2)', $module, $ratio) . PHP_EOL;
        }
        return $text;
    }
}