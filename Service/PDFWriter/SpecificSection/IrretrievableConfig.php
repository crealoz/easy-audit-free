<?php


namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter;

class IrretrievableConfig extends PDFWriter\SpecificSection\AbstractSection implements \Crealoz\EasyAudit\Api\Result\SectionInterface
{

    /**
     * @inheritDoc
     */
    protected function writeContent(PDFWriter $pdfWriter, array $subresults): void
    {
        $pdfWriter->writeLine($this->getLine('', $subresults));
    }

    /**
     * @inheritDoc
     */
    public function calculateSize(array $subresults): int
    {
        $size = $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize([$subresults]);
        $size += $this->sizeCalculation->getSizeForText($this->getLine('', $subresults['files']));
        return $size;
    }

    public function getLine(string $key, mixed $entry): string
    {
        return __('%1 modules configuration cannot be retrieved', count($entry));
    }

    public function getPHPFormatedText(string $key, array $subResults): string
    {
        return $this->getLine($key, $subResults);
    }
}
