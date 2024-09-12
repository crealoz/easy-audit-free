<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\PDFWriter\CliTranslator;
use Crealoz\EasyAudit\Service\PDFWriter\SizeCalculation;
use Crealoz\EasyAudit\Service\PDFWriter\SpecificSection\SectionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Crealoz\EasyAudit\Service\PDFWriter\SpecificSections;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class PDFWriter
{

    private \Zend_Pdf $pdf;

    public \Zend_Pdf_Page $currentPage;

    public int $y;

    private ?\Zend_Pdf_Resource_Image $logo;

    public function __construct(
        private readonly Filesystem       $filesystem,
        private readonly SizeCalculation  $sizeCalculation,
        private readonly Reader           $moduleReader,
        private readonly CliTranslator    $cliTranslator,
        private readonly array $specificSections = [],
        public int                        $x = 50
    )
    {

    }

    /**
     * Entry point for the PDF creation
     *
     */
    public function createdPDF($results, $locale): string
    {
        $this->pdf = new \Zend_Pdf();
        $imagePath = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, 'Crealoz_EasyAudit') . '/adminhtml/web/images/crealoz-logo-dark.png';
        try {
            $this->logo = \Zend_Pdf_Image::imageWithPath($imagePath);
        } catch (\Zend_Pdf_Exception $e) {
            $this->logo = null;
        }
        $this->cliTranslator->initLanguage($locale);
        $erroneousFiles = $results['erroneousFiles'];
        unset($results['erroneousFiles']);
        foreach ($results as $type => $result) {
            foreach ($result as $section => $sectionResults) {
                $isFirst = true;
                foreach ($sectionResults as $subsection => $subResults) {
                    if (is_array($subResults) && !empty($subResults) && ($subResults['hasErrors'])) {
                        if ($isFirst) {
                            $this->addPage();
                            $this->writeTitle($section, 40);
                            $isFirst = false;
                        }
                        if ($this->y < 140 + $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize($subResults, true)) {
                            $this->addPage();
                        }
                        $this->writeSectionTitle($subsection);
                        $this->manageSubResult($subResults);
                    }
                }
            }
        }
        //Get media directory in filesystem
        if (!$this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist('/crealoz')) {
            $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->create('/crealoz');
        }
        $fileName = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('/crealoz/audit.pdf');
        $this->pdf->save($fileName);
        return $fileName;
    }

    /**
     * Display the header of the PDF with the logo of the company.
     * @return void
     */
    private function makeHeaderAndFooter(): void
    {
        $this->currentPage->drawText(__('EasyAudit Report by Crealoz'), 20, 20);
        // Get the image path
        if ($this->logo !== null) {
            $this->currentPage->drawImage($this->logo, 500, 800, 550, 820);
        }
        $this->currentPage->drawText('Created on : ' . date('Y-m-d H:i:s'), 420, 20);
    }

    /**
     * Manage the subresult of a section
     *
     * @param array $subResults
     */
    private function manageSubResult($subResults): void
    {
        if (!empty($subResults['errors'])) {
            if ($this->y < $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize($subResults['errors'])) {
                $this->addPage();
            }
            $this->setErrorStyle(14);
            $this->displaySection('Errors', $subResults['errors']);
        }
        if (!empty($subResults['warnings'])) {
            if ($this->y < $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize($subResults['warnings'])) {
                $this->addPage();
            } else {
                $this->y -= 15;
            }
            $this->setWarningStyle(14);
            $this->displaySection('Warnings', $subResults['warnings']);
        }
        if (!empty($subResults['suggestions'])) {
            if ($this->y < $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize($subResults['suggestions'])) {
                $this->addPage();
            } else {
                $this->y -= 15;
            }
            $this->setGeneralStyle(14);
            $this->displaySection('Suggestions', $subResults['suggestions']);
        }
    }

    private function displaySection(string $title, array $section): void
    {
        $translatedTitle = $this->cliTranslator->translate($title);
        $this->currentPage->drawText($translatedTitle, 44, $this->y);
        foreach ($section as $type => $entries) {
            if (isset($entries['specificSections'])) {
                $sectionName = $entries['specificSections'];
                unset($entries['specificSections']);
                if (!isset($this->specificSections[$sectionName]) || !$this->specificSections[$sectionName] instanceof SectionInterface) {
                    throw new \InvalidArgumentException("Specific section $sectionName is not valid");
                }
                $this->specificSections[$sectionName]->writeSection($this, $entries);
            } else {
                $this->manageSubsection($entries);
            }
        }
    }

    private function manageSubsection($subresults): void
    {
        if ($subresults['files'] === []) {
            return;
        }
        $this->writeSubSectionIntro($subresults);
        $this->writeLine('Files:');
        foreach ($subresults['files'] as $key => $files) {
            if (is_array($files)) {
                $this->writeLine($key);
                foreach ($files as $file) {
                    $this->writeLine('-' . $file, 0, 8, 0.2, 0.2, 0.2);
                }
            } else {
                $this->writeLine('-' . $files);
            }
        }
    }

    public function writeLine($text, $depth = 0, $size = 9, $r = 0, $g = 0, $b = 0): void
    {
        $translatedText = $text;
        if ($depth == 0) {
            $translatedText = $this->cliTranslator->translate($text);
        }
        $this->setGeneralStyle($size, $r, $g, $b);
        // If line is too long, we split it
        if (strlen($text) > 130) {
            $wrappedText = wordwrap($text, 130, "--SPLIT--");
            $lines = explode("--SPLIT--", $wrappedText);
            $depth++;
            foreach ($lines as $line) {
                $this->writeLine($line, $depth);
            }
            return;
        }
        $this->currentPage->drawText($translatedText, $this->x, $this->y);
        $this->y -= floor($size * 1.3);
        if ($this->y < 50) {
            $this->addPage();
        }
    }

    private function writeTitle($text, $x = null): void
    {
        $translatedText = $this->cliTranslator->translate($text);
        $this->y -= 10;
        if ($this->y < 130) {
            $this->addPage();
        }
        $x = $x ?? $this->x;
        $this->setTitleStyle();
        $this->y -= 15;
        $this->currentPage->drawText(strtoupper($translatedText), $x, $this->y);
        $this->y -= 30;
        $this->setGeneralStyle();
    }

    public function writeSubSectionIntro($subsection): void
    {
        if ($this->y < $this->sizeCalculation->calculateIntroSize($subsection)) {
            $this->addPage();
        }
        if (isset($subsection['title'])) {
            $this->y -= 20;
            $this->setSubTitleStyle();
            $translatedTitle = $this->cliTranslator->translate($subsection['title']);
            $this->currentPage->drawText($translatedTitle, 48, $this->y);
        }
        if (isset($subsection['explanation'])) {
            // First we remove line feed, carriage return and tabs
            $subsection['explanation'] = preg_replace('/\s+/', ' ', $subsection['explanation']);
            $this->y -= 10;
            $this->writeLine($subsection['explanation']);
        }
        if (isset($subsection['caution'])) {
            $subsection['caution'] = preg_replace('/\s+/', ' ', $subsection['caution']);
            $this->writeLine($subsection['caution'],0, 8, 0.85, 0, 0);
        }
    }

    private function writeSectionTitle($text): void
    {
        $this->setTitleStyle(15);
        $this->y -= 15;
        $translatedText = $this->cliTranslator->translate($text);
        $this->currentPage->drawText($translatedText, 43, $this->y);
        $this->y -= 20;
        if ($this->y < 50) {
            $this->addPage();
        }
        $this->setGeneralStyle();
    }

    public function addPage()
    {
        $this->currentPage = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $this->currentPage;
        $this->setGeneralStyle();
        $this->y = 850 - 50;
        $this->makeHeaderAndFooter();
    }

    public function setGeneralStyle($size = 9, $r = 0, $g = 0, $b = 0)
    {
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb($r, $g, $b));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb($r, $g, $b));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    private function setTitleStyle($size = 20)
    {
        $style = new \Zend_Pdf_Style();
        // Blue color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0.85));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0.85));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    private function setSubTitleStyle($size = 12)
    {
        $style = new \Zend_Pdf_Style();
        // Blue color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0.45, 0.85));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0.45, 0.85));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    private function setErrorStyle($size = 11)
    {
        $style = new \Zend_Pdf_Style();
        // Red color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0.85, 0, 0));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0.85, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    private function setWarningStyle($size = 11)
    {
        $style = new \Zend_Pdf_Style();
        // Orange color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0.85, 0.45, 0));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0.85, 0.45, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }
}