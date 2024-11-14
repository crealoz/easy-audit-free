<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\PDFWriter\CliTranslator;
use Crealoz\EasyAudit\Service\PDFWriter\SizeCalculation;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class PDFWriter
{

    private \Zend_Pdf $pdf;

    public \Zend_Pdf_Page $currentPage;

    public int $y = 0;

    private ?\Zend_Pdf_Resource_Image $logo;

    const MEDIA_FOLDER = '/crealoz';
    private int $columnWidth;
    private int $currentColumn = 0;
    private int $columnX;

    private int $columnY = 800;

    private int $annexNumber = 1;
    private array $annexes = [];

    public function __construct(
        private readonly Filesystem       $filesystem,
        private readonly SizeCalculation  $sizeCalculation,
        private readonly CliTranslator    $cliTranslator,
        private readonly \Psr\Log\LoggerInterface $logger,
        private readonly Reader           $moduleReader,
        private readonly array $specificSections = [],
        public int                        $x = 50,
        public int                        $columnCount = 1
    )
    {
        $this->columnWidth = (int)((595 - 2 * $this->x) / $this->columnCount); // A4 width is 595 points
        $this->columnX = $this->x;
    }

    /**
     * Entry point for the PDF creation
     *
     * @throws FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    public function createdPDF($results, $locale, $filename): string
    {
        $this->logger->debug('Starting to create the PDF');
        $this->pdf = new \Zend_Pdf();
        $imagePath = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, 'Crealoz_EasyAudit') . '/adminhtml/web/images/crealoz-logo-dark.png';
        try {
            $this->logo = \Zend_Pdf_Image::imageWithPath($imagePath);
        } catch (\Zend_Pdf_Exception $e) {
            $this->logger->error('Error while loading the logo: ' . $e->getMessage());
            $this->logo = null;
        }
        $this->cliTranslator->initLanguage($locale);
        $erroneousFiles = $results['erroneousFiles'];
        $introductions = $results['introduction'] ?? [];
        $this->addPage();
        $this->logger->debug('Starting to write the introduction');
        foreach ($introductions as $introduction) {
            $this->manageIntroduction($introduction);
        }
        unset($results['introduction']);
        unset($results['erroneousFiles']);
        $this->logger->debug('Starting to write the sections');
        foreach ($results as $section => $sectionResults) {
            $isFirst = true;
            foreach ($sectionResults as $subsection => $subResults) {
                if (is_array($subResults) && !empty($subResults) && ($subResults['hasErrors'])) {
                    if ($isFirst) {
                        if ($this->columnCount !== 1) {
                            $this->setColumnCount(1);
                        } else {
                            $this->addPage();
                        }
                        $this->writeTitle($section, 40);
                        $isFirst = false;
                    }
                    $this->writeSectionTitle($subsection);
                    $this->manageSubResult($subResults);
                }
            }
        }
        $this->logger->debug('Writing annexes');
        $this->writeAnnexes();
        //Get media directory in filesystem
        if (!$this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist(self::MEDIA_FOLDER)) {
            $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->create(self::MEDIA_FOLDER);
        }
        $this->logger->debug('Saving the PDF file in the media folder');
        $filePath = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath(self::MEDIA_FOLDER . DIRECTORY_SEPARATOR . $filename . '.pdf');
        // Check if the file already exists
        if ($this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist(self::MEDIA_FOLDER . DIRECTORY_SEPARATOR . $filename . '.pdf')) {
            $this->logger->debug('File already exists, adding timestamp to the filename');
            $filename = $filename . '_' . time();
            $filePath = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath(self::MEDIA_FOLDER . DIRECTORY_SEPARATOR . $filename . '.pdf');
        }
        $this->logger->debug('Saving the PDF file in ' . $filePath);
        $this->pdf->save($filePath);
        return $filePath;
    }

    /**
     * Display the header of the PDF with the logo of the company.
     * @return void
     * @throws \Zend_Pdf_Exception
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
     * @throws \Zend_Pdf_Exception
     */
    private function manageIntroduction(array $introduction): void
    {
        if ($this->columnCount !== 1) {
            $this->setColumnCount(1);
        }
        if (!is_array($introduction['summary'])) {
            throw new \InvalidArgumentException('Introduction summary must be an array');
        }
        $paragraphs = $introduction['summary'];
        foreach ($paragraphs as $paragraph) {
            $this->writeLine($paragraph);
        }
        $this->setColumnCount(2);
        foreach ($introduction['files'] ?? [] as $scope => $files) {
            if ($this->y < 50) {
                $this->switchColumnOrAddPage();
            }
            $this->writeLine(ucfirst($scope) . ' files:');
            foreach ($files as $file => $score) {
                $file = $this->stripVendorOrApp($file);
                if ($score >= 10) {
                    $this->writeLine($file . ' (' . $score . ')', true,8, 0, 0.85);
                } elseif ($score >= 5) {
                    $this->writeLine($file . ' (' . $score . ')', true, 7, 0, 0.85, 0.45);
                } else {
                    $this->writeLine($file . ' (' . $score . ')', true, 6, 0, 0.85, 0.85);
                }
            }
        }
    }

    private function stripVendorOrApp($path): string
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        if (isset($pathParts[0]) && in_array($pathParts[0], ['vendor', 'app'])) {
            $offset = $pathParts[0] === 'vendor' ? 1 : 2;
            return implode(DIRECTORY_SEPARATOR, array_slice($pathParts, $offset));
        }
        return $path;
    }

    /**
     * Manage the subresult of a section
     *
     * @param array $subResults
     * @throws \Zend_Pdf_Exception
     */
    private function manageSubResult(array $subResults): void
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

    /**
     * @throws \Zend_Pdf_Exception
     */
    private function displaySection(string $title, array $section): void
    {
        $translatedTitle = $this->cliTranslator->translate($title);
        if ($this->columnCount !== 1) {
            $this->setColumnCount(1);
        }
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

    /**
     * @throws \Zend_Pdf_Exception
     */
    private function manageSubsection($subResults): void
    {
        if ($subResults['files'] === []) {
            return;
        }
        $this->writeSubSectionIntro($subResults);
        $numberOfPages = $this->sizeCalculation->getNumberOfPagesForFiles($subResults['files']);
        if ($numberOfPages > 10 && is_array($subResults['files'])) {
            $this->delegateToAnnex($numberOfPages, $subResults['files'], $subResults['title'] ?? '');
        } else {
            $this->writeLine('Files:');
            $this->manageFiles($numberOfPages, $subResults['files']);
        }
        if ($this->columnCount !== 1) {
            $this->setColumnCount(1);
        }
    }

    /**
     * @param $numberOfPages
     * @param $key
     * @param $files
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    private function manageFiles($numberOfPages, $resultFiles): void
    {
        if ($numberOfPages > 1) {
            $this->setColumnCount(2);
        }
        foreach ($resultFiles as $key => $files) {
            if (is_array($files)) {
                $this->writeLine($key, true);
                foreach ($files as $file) {
                    $file = $this->stripVendorOrApp($file);
                    $this->writeLine('-' . $file, true, 8, 0, 0.2, 0.2, 0.2);
                }
                $this->y -= 5;
            } else {
                $file = $this->stripVendorOrApp($files);
                $this->writeLine('-' . $file, true);
            }
        }
    }

    /**
     * @param int $pages
     * @param $key
     * @param $files
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function delegateToAnnex(int $pages, $files, $description): void
    {
        if (!is_array($files)) {
            throw new \InvalidArgumentException('Files must be an array to be delegated to annex.');
        }
        $this->annexes[$this->annexNumber] = ['pages' => $pages, 'files' => $files, 'description' => $description];
        $this->writeLine(__('See annexe %1 for more details.', $this->annexNumber));
        $this->annexNumber++;
    }

    /**
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    private function writeAnnexes(): void
    {
        foreach ($this->annexes as $annexNumber => $annex) {
            $this->addPage();
            $this->writeTitle('Annex ' . $annexNumber);
            $this->writeLine($annex['description']);
            $this->manageFiles($annex['pages'], $annex['files']);
            if ($this->columnCount !== 1) {
                $this->setColumnCount(1);
            }
        }
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
    public function writeLine($text, $isFile = false, $size = 9, $depth = 0, $r = 0, $g = 0, $b = 0): void
    {
        $translatedText = trim($text);
        if ($depth == 0) {
            $translatedText = $this->cliTranslator->translate($text);
        }
        $this->setGeneralStyle($size, $r, $g, $b);
        // If line is too long, we split it
        $availableWidth = 130 / $this->columnCount;
        if ($depth == 0 && strlen($text) > $availableWidth) {
            if ($isFile) {
                $lastSlashPos = strrpos(substr($text, 0, $availableWidth), '/');
                if ($lastSlashPos !== false) {
                    $lines = [substr($text, 0, $lastSlashPos + 1), substr($text, $lastSlashPos + 1)];
                } else {
                    $lines = str_split($text, $availableWidth - 30);
                }
            } else {
                $wrappedText = wordwrap($text, $availableWidth, "--SPLIT--");
                $lines = explode("--SPLIT--", $wrappedText);
            }
            $depth++;
            foreach ($lines as $line) {
                $this->writeLine($line, $isFile, $size, $depth, $r, $g, $b);
            }
            return;
        }
        $this->currentPage->drawText($translatedText, $this->columnX, $this->y);
        $this->y -= floor($size * 1.3);
        if ($this->y < 50) {
            $this->switchColumnOrAddPage();
        }
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
    public function switchColumnOrAddPage(): void
    {
        $this->currentColumn++;
        if ($this->currentColumn >= $this->columnCount) {
            $this->addPage();
            $this->currentColumn = 0;
            $this->y = 800;
            $this->columnY = $this->y;
        } else {
            $this->y = $this->columnY;
        }
        $this->columnX = $this->x + $this->currentColumn * $this->columnWidth;
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
    public function setColumnCount(int $columnCount): void
    {
        if ($columnCount !== $this->columnCount) {
            if ($columnCount == 1 && $this->columnCount > 1) {
                $this->addPage();
            }
            $this->columnCount = $columnCount;
            $this->columnWidth = (int)((595 - 2 * $this->x) / $this->columnCount); // A4 width is 595 points
            $this->columnY = $this->y;
            $this->currentColumn = 0;
            $this->columnX = $this->x;
        }
    }



    /**
     * @throws \Zend_Pdf_Exception
     */
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

    /**
     * @throws \Zend_Pdf_Exception
     */
    public function writeSubSectionIntro($subsection): void
    {
        if ($this->y < $this->sizeCalculation->calculateSectionIntroSize($subsection)) {
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
            $this->writeLine($subsection['caution'], false, 8, 0, 0.85, 0, 0);
        }
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
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

    /**
     * @throws \Zend_Pdf_Exception
     */
    public function addPage(): void
    {
        if ($this->y !== 800) {
            $this->currentPage = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
            $this->pdf->pages[] = $this->currentPage;
            $this->setGeneralStyle();
            $this->y = 800;
            $this->makeHeaderAndFooter();
        }
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
    public function setGeneralStyle($size = 9, $r = 0, $g = 0, $b = 0): void
    {
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb($r, $g, $b));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb($r, $g, $b));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
    private function setTitleStyle($size = 20): void
    {
        $style = new \Zend_Pdf_Style();
        // Blue color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0.85));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0.85));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
    private function setSubTitleStyle($size = 12): void
    {
        $style = new \Zend_Pdf_Style();
        // Blue color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0.45, 0.85));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0.45, 0.85));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
    private function setErrorStyle($size = 11): void
    {
        $style = new \Zend_Pdf_Style();
        // Red color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0.85, 0, 0));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0.85, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    /**
     * @throws \Zend_Pdf_Exception
     */
    private function setWarningStyle($size = 11): void
    {
        $style = new \Zend_Pdf_Style();
        // Orange color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0.85, 0.45, 0));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0.85, 0.45, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $this->currentPage->setStyle($style);
    }

    public function getColumnY(): int
    {
        return $this->columnY;
    }

    public function setColumnY(int $columnY): void
    {
        $this->columnY = $columnY;
    }

    public function getColumnX(): int
    {
        return $this->columnX;
    }

    public function setColumnX(int $columnX): void
    {
        $this->columnX = $columnX;
    }
}