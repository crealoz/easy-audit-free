<?php


namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter;

class FlatCatalog extends PDFWriter\SpecificSection\AbstractSection implements \Crealoz\EasyAudit\Api\Result\SectionInterface
{

    /**
     * @inheritDoc
     */
    protected function writeContent(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeLine($this->getFlatCatalogProduct());
        foreach ($subresults['products'] as $storeName) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3);
            $pdfWriter->writeLine($storeName);
        }
        $pdfWriter->writeLine($this->getFlatCatalogCategory());
        foreach ($subresults['categories'] as $storeName) {
            $this->manageColumnPage($pdfWriter, 9 * 1.3);
            $pdfWriter->writeLine($storeName);
        }
    }

    /**
     * @inheritDoc
     */
    public function calculateSize(array $subresults): int
    {
        $size = $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize([$subresults]);
        $size += $this->sizeCalculation->getSizeForText($this->getFlatCatalogProduct());
        foreach ($subresults['products'] as $storeName) {
            $size += $this->sizeCalculation->getSizeForText($storeName);
        }
        $size += $this->sizeCalculation->getSizeForText($this->getFlatCatalogCategory());
        foreach ($subresults['categories'] as $storeName) {
            $size += $this->sizeCalculation->getSizeForText($storeName);
        }
        return $size;
    }

    public function getLine(string $key, mixed $entry): string
    {
        $text = $this->getFlatCatalogProduct();
        foreach ($entry['products'] as $storeName) {
            $text .= $storeName;
        }
        $text .= $this->getFlatCatalogCategory();
        foreach ($entry['categories'] as $storeName) {
            $text .= $storeName;
        }
        return $text;
    }

    private function getFlatCatalogProduct() : string
    {
        return __('Flat catalog enabled for Products:');
    }

    private function getFlatCatalogCategory() : string
    {
        return __('Flat catalog enabled for Categories:');
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        $text = $this->getFlatCatalogProduct() . PHP_EOL;
        foreach ($subResults['products'] as $storeName) {
            $text .= $storeName . PHP_EOL;
        }
        $text .= $this->getFlatCatalogCategory() . PHP_EOL;
        foreach ($subResults['categories'] as $storeName) {
            $text .= $storeName . PHP_EOL;
        }
        return $text;
    }
}
