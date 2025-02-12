<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter;

class HelperInsteadOfViewModel extends PDFWriter\SpecificSection\AbstractSection implements \Crealoz\EasyAudit\Api\Result\SectionInterface
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
    protected function writeContent(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeLine(__('Files:'));
        foreach ($subresults['files'] as $file => $usages) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3 + $usages['usageCount'] * 12 + 10);
            $pdfWriter->writeLine($this->getFileLine($file, $usages));
            $pdfWriter->x += 5;
            unset($usages['usageCount']);
            foreach ($usages as $template => $usage) {
                $this->styleManager->setGeneralStyle($pdfWriter->currentPage, 8);
                $pdfWriter->currentPage->drawText($this->getTemplateLine($template, $usage), $pdfWriter->x, $pdfWriter->y);
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
        $text = $this->getFileLine($key, $entry);
        unset($entry['usageCount']);
        $templates = $entry;
        foreach ($templates as $template => $usage) {
            $text .= PHP_EOL . $this->getTemplateLine($template, $usage);
        }
        return $text;
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = __('Files') . PHP_EOL;
        foreach ($subResults as $file => $usages) {
            $text .= $this->getFileLine($file, $usages) . PHP_EOL;
            unset($usages['usageCount']);
            foreach ($usages as $template => $usage) {
                $text .= $this->getTemplateLine($template, $usage) . PHP_EOL;
            }
        }
        return $text;
    }

    private function getFileLine(string $file, array $usages): string
    {
        return __('- %1 (usages: %2)', $this->modulePaths->stripVendorOrApp($file), $usages['usageCount']);
        }

    private function getTemplateLine(string $template, int $usage): string
    {
        return '  -' . $this->modulePaths->stripVendorOrApp($template) . '(' . $usage . ')';
    }
}