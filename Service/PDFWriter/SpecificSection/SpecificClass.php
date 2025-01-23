<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter;

class SpecificClass extends AbstractSection implements SectionInterface
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
            $this->manageColumnPage($pdfWriter, 9 * 1.3);
            $pdfWriter->writeLine($this->getLine($file, $arguments));
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
            $size += $this->sizeCalculation->getSizeForText($this->getLine($file, $arguments));
        }
        return $size;
    }

    /**
     * @inheritdoc
     * @param mixed $entry
     */
    public function getLine($key, $entry): string
    {
        return __('- %1 (potential issues count : %2)', $this->modulePaths->stripVendorOrApp($key), count($entry));
    }

    /**
     * @inheritdoc
     */
    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = __('Files') . PHP_EOL;
        foreach ($subResults as $file => $arguments) {
            $text .= $this->getLine($file, $arguments) . PHP_EOL;
        }
        return $text;
    }
}