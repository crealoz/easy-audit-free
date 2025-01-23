<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter;

class SpecificModel extends AbstractSection implements SectionInterface
{

    /**
     * @readonly
     */
    private ModulePaths $modulePaths;
    public function __construct(
        PDFWriter\SizeCalculation $sizeCalculation,
        ModulePaths $modulePaths
    )
    {
        $this->modulePaths = $modulePaths;
        parent::__construct($sizeCalculation);
    }

    /**
     * @inheritDoc
     */
    protected function writeContent(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeLine('Files:');
        foreach ($subresults['files'] as $file => $arguments) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3 + count($arguments) * 12 + 10);
            $pdfWriter->writeLine($this->modulePaths->stripVendorOrApp($file), true);
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

    /**
     * @param mixed $entry
     */
    public function getLine($key, $entry): string
    {
        if (!is_array($entry)) {
            return '-' . $entry;
        }
        $text = '-' . $key . PHP_EOL;
        foreach ($entry as $argument) {
            $text .= '  -' . $argument . PHP_EOL;
        }
        return $text;
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = __('Files') . PHP_EOL;
        foreach ($subResults as $file => $arguments) {
            $text .= $this->getLine($file, $arguments) . PHP_EOL;
        }
        return $text;
    }
}