<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Api\Result\SectionInterface;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter\SizeCalculation;
use Crealoz\EasyAudit\Service\PDFWriter\SpecificSectionGetter;
use Crealoz\EasyAudit\Service\PDFWriter\StyleManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;

/**
 * Class PDFWriter
 * It is responsible for writing the PDF file. It will manage the creation of the PDF, the writing of the different sections.
 *
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
    /**
     * @var true
     */
    private bool $logoInitialized = false;

    public function __construct(
        private readonly Filesystem       $filesystem,
        private readonly SizeCalculation  $sizeCalculation,
        private readonly \Psr\Log\LoggerInterface $logger,
        private readonly Reader           $moduleReader,
        private readonly ModulePaths      $modulePaths,
        private readonly StyleManager     $styleManager,
        private readonly SpecificSectionGetter $specificSectionGetter,
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
    public function createdPDF($results, $filename): string
    {
        $this->logger->debug('Starting to create the PDF ' . $filename);
        $this->pdf = new \Zend_Pdf();

        $this->addPage();
        if (!empty($results['introduction']) && is_array($results['introduction'])) {
            $this->logger->debug('Starting to write the introduction');
            foreach ($results['introduction'] as $introduction) {
                $this->manageIntroduction($introduction);
            }
        }
        unset($results['introduction']);
        unset($results['erroneousFiles']);
        $this->logger->debug('Starting to write the sections');
        foreach ($results as $section => $sectionResults) {
            if ($section === 'data') {
                continue;
            }
            $isFirst = true;
            ksort($sectionResults);
            foreach ($sectionResults as $subsection => $subResults) {
                if (is_array($subResults) && $subResults !== [] && ($subResults['hasErrors'])) {
                    if ($isFirst) {
                        if ($this->columnCount !== 1) {
                            $this->setColumnCount(1);
                        } else {
                            $this->addPage();
                        }
                        $this->writeTitle($section, 40);
                        $isFirst = false;
                    }
                    $this->writeSectionTitle($subResults['title']);
                    unset($subResults['title']);
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

    private function getLogo()
    {
        if (!$this->logoInitialized) {
            try {
                if (!isset($this->logo)) {
                    $imagePath = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, 'Crealoz_EasyAudit') . '/adminhtml/web/images/crealoz-logo-dark.png';
                    $this->logo = \Zend_Pdf_Image::imageWithPath($imagePath);
                    $this->logoInitialized = true;
                }
            } catch (\Zend_Pdf_Exception $e) {
                $this->logger->error('Error while loading the logo: ' . $e->getMessage());
                $this->logo = null;
                $this->logoInitialized = true;
            }
        }
        return $this->logo;
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
        if ($this->getLogo() !== null) {
            $this->currentPage->drawImage($this->getLogo(), 500, 800, 550, 820);
        }
        $this->currentPage->drawText('Created on : ' . date('Y-m-d H:i:s'), 420, 20);
    }

    /**
     * Manage the introduction of the PDF. It will display disclaimers, introduction and the most problematic files
     * using a score.
     *
     * @param array $introduction
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
            if ($paragraph instanceof \Magento\Framework\Phrase) {
                $paragraph = __($paragraph);
            }
            $this->writeLine($paragraph);
        }
        $this->setColumnCount(2);
        foreach ($introduction['files'] ?? [] as $scope => $files) {
            if ($this->y < 50) {
                $this->switchColumnOrAddPage();
            }
            $this->writeLine(ucfirst((string) $scope) . ' files:');
            foreach ($files as $file => $score) {
                $file = $this->modulePaths->stripVendorOrApp($file);
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
            $this->styleManager->setErrorStyle($this->currentPage, 14);
            $this->displaySection('Errors', $subResults['errors']);
        }
        if (!empty($subResults['warnings'])) {
            if ($this->y < $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize($subResults['warnings'])) {
                $this->addPage();
            } else {
                $this->y -= 15;
            }
            $this->styleManager->setWarningStyle($this->currentPage, 14);
            $this->displaySection('Warnings', $subResults['warnings']);
        }
        if (!empty($subResults['suggestions'])) {
            if ($this->y < $this->sizeCalculation->calculateTitlePlusFirstSubsectionSize($subResults['suggestions'])) {
                $this->addPage();
            } else {
                $this->y -= 15;
            }
            $this->styleManager->setGeneralStyle($this->currentPage, 14);
            $this->displaySection('Suggestions', $subResults['suggestions']);
        }
    }

    /**
     * Display a section of the PDF. If the section is too big, it will be displayed in an annex.
     *
     * @param string $title the title of the section
     * @param array $section the section to display
     * @throws \Zend_Pdf_Exception
     */
    private function displaySection(string $title, array $section): void
    {
        if ($this->columnCount !== 1) {
            $this->setColumnCount(1);
        }
        if ($this->shouldDisplaySectionTitle($section)) {
            $this->currentPage->drawText($title, 44, $this->y);
        } else {
            return;
        }
        foreach ($section as $entries) {
            if (isset($entries['specificSections'])) {
                $sectionName = $entries['specificSections'];
                unset($entries['specificSections']);
                $specificSection = $this->specificSectionGetter->getSpecificSection($sectionName);
                if ($entries['files'] === []) {
                    continue;
                }
                $numberOfPages = $specificSection->calculateSize($entries) / 800;
                if ($numberOfPages > 10) {
                    if (!is_int($numberOfPages)) {
                        $numberOfPages = (int)ceil($numberOfPages);
                    }
                    $this->delegateToAnnex($numberOfPages, $entries, $title, $sectionName);
                } else {
                    $specificSection->writeSection($this, $entries);
                }
            } else {
                $this->manageSubsection($entries);
            }
        }
    }

    private function shouldDisplaySectionTitle(array $section): bool
    {
        $shouldDisplay = false;
        foreach ($section as $entries) {
            if (!empty($entries['files'])) {
                $shouldDisplay = true;
                break;
            }
        }
        return $shouldDisplay;
    }

    /**
     * The generic function to write subsections. It displays the title, the explanation and the files.
     *
     * @param array $subResults
     * @throws \Zend_Pdf_Exception
     */
    private function manageSubsection(array $subResults): void
    {
        if ($subResults['files'] === []) {
            return;
        }
        $this->writeSubSectionIntro($subResults);
        $numberOfPages = $this->sizeCalculation->getNumberOfPagesForFiles($subResults['files']);
        if ($numberOfPages > 10 && is_array($subResults['files'])) {
            if (!is_int($numberOfPages)) {
                $numberOfPages = (int)ceil($numberOfPages);
            }
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
     * Function to manage the files to display in a section. It is use for main sections and annexes
     *
     * @param int $numberOfPages the number of pages the section will take
     * @param array|string $resultFiles the files to display (can be other things than files)
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    private function manageFiles(int $numberOfPages, array|string $resultFiles): void
    {
        if ($numberOfPages > 1) {
            $this->setColumnCount(2);
        }
        foreach ($resultFiles as $key => $files) {
            if (is_array($files)) {
                $this->writeLine($key, true);
                foreach ($files as $file) {
                    $file = $this->modulePaths->stripVendorOrApp($file);
                    $this->writeLine('-' . $file, true, 8, 0, 0.2, 0.2, 0.2);
                }
                $this->y -= 5;
            } else {
                $file = $this->modulePaths->stripVendorOrApp($files);
                $this->writeLine('-' . $file, true);
            }
        }
    }

    /**
     * @param int $pages the number of pages the annex will take
     * @param array $files the files to display (can be other things than files)
     * @param string $description the description of the annex
     * @param null $specificSection in case a specific display for the section is needed
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function delegateToAnnex(int $pages, array $files, string $description, $specificSection = null): void
    {
        $this->annexes[$this->annexNumber] = ['pages' => $pages, 'files' => $files, 'description' => $description, 'specificSection' => $specificSection];
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
            if (isset($annex['specificSection'])) {
                /** @var SectionInterface $specificSection */
                $specificSection = $this->specificSectionGetter->getSpecificSection($annex['specificSection']);
                $specificSection->writeSection($this, $annex['files'], true);
            } else {
                $this->manageFiles($annex['pages'], $annex['files']);
            }
            if ($this->columnCount !== 1) {
                $this->setColumnCount(1);
            }
        }
    }

    /**
     * Write a line of text in the PDF. If the line is too long, it will be split in multiple lines. Using the translator,
     * it will try to translate the text.
     *
     * @param string $text text to write
     * @param bool $isFile either the text is a file path or not
     * @param int $size font size
     * @param int $depth depth of the recursion
     * @param float $r red color balance between 0 and 1
     * @param float $g green color balance between 0 and 1
     * @param float $b blue color balance between 0 and 1
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function writeLine(string $text, bool $isFile = false, int $size = 9, int $depth = 0, float $r = 0, float $g = 0, float $b = 0): void
    {
        $text = trim($text);
        $this->styleManager->setGeneralStyle($this->currentPage, $size, $r, $g, $b);
        // If line is too long, we split it
        $availableWidth = 130 / $this->columnCount;
        if ($depth == 0 && strlen($text) > $availableWidth) {
            foreach ($this->getLines($text, $availableWidth, $isFile) as $line) {
                $this->writeText($line, $size);
            }
        } else {
            $this->writeText($text, $size);
        }
    }

    private function writeText(string $text, int $size): void
    {
        $this->currentPage->drawText($text, $this->columnX, $this->y);
        $this->y -= floor($size * 1.3);
        if ($this->y < 50) {
            $this->switchColumnOrAddPage();
        }
    }

    private function getLines(string $text, int $availableWidth, bool $isFile): array
    {
        $lines = [];
        while (strlen($text) > $availableWidth) {
            if ($isFile) {
                $lastSlashPos = strrpos(substr($text, 0, $availableWidth), '/');
                if ($lastSlashPos !== false) {
                    $lines[] = substr($text, 0, $lastSlashPos + 1);
                    $text = substr($text, $lastSlashPos + 1);
                } else {
                    $lines[] = substr($text, 0, $availableWidth - 30); // Ajustement pour $availableWidth
                    $text = substr($text, $availableWidth - 30);
                }
            } else {
                $wrappedText = wordwrap($text, $availableWidth, "--SPLIT--", true);
                $splitLines = explode("--SPLIT--", $wrappedText);
                $lines = array_merge($lines, $splitLines);
                $text = "";
            }
        }

        if ($text !== '' && $text !== '0') {
            $lines[] = $text;
        }

        return $lines;
    }

    /**
     * Check if we need to switch column or add a new page.
     *
     * @return void
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
     * Change the number of columns. If the number of columns is reduced to 1, a new page is added.
     *
     * @param int $columnCount
     * @return void
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
     * Try to translate the title and write it in the PDF.
     *
     * @param $text
     * @param $x
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    private function writeTitle($text, $x = null): void
    {
        $this->y -= 10;
        if ($this->y < 130) {
            $this->addPage();
        }
        $x = $x ?? $this->x;
        $this->styleManager->setTitleStyle($this->currentPage);
        $this->y -= 15;
        $this->currentPage->drawText(strtoupper((string) $text), $x, $this->y);
        $this->y -= 30;
        $this->styleManager->setGeneralStyle($this->currentPage);
    }

    /**
     * Write the introduction of a subsection. It will display the title, the explanation and the caution if any relevant.
     *
     * @param array $subsection
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function writeSubSectionIntro(array $subsection): void
    {
        if ($this->y < $this->sizeCalculation->calculateSectionIntroSize($subsection)) {
            $this->addPage();
        }
        if (isset($subsection['title'])) {
            $this->y -= 20;
            $this->styleManager->setSubTitleStyle($this->currentPage);
            $this->currentPage->drawText($subsection['title'], 48, $this->y);
        }
        if (isset($subsection['explanation'])) {
            // First we remove line feed, carriage return and tabs
            $subsection['explanation'] = preg_replace('/\s+/', ' ', (string) $subsection['explanation']);
            $this->y -= 10;
            $this->writeLine($subsection['explanation']);
        }
        if (isset($subsection['caution'])) {
            $subsection['caution'] = preg_replace('/\s+/', ' ', (string) $subsection['caution']);
            $this->writeLine($subsection['caution'], false, 8, 0, 0.85, 0, 0);
        }
    }

    /**
     * Write the title of a section. It will be displayed in blue.
     *
     * @param string $text
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    private function writeSectionTitle(string $text): void
    {
        $this->styleManager->setTitleStyle($this->currentPage, 15);
        $this->y -= 15;
        $this->currentPage->drawText($text, 43, $this->y);
        $this->y -= 20;
        if ($this->y < 50) {
            $this->addPage();
        }
        $this->styleManager->setGeneralStyle($this->currentPage);
    }

    /**
     * Adds a page to the pdf and adds the footer and header to it.
     *
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function addPage(): void
    {
        if ($this->y !== 800) {
            $this->currentPage = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
            $this->pdf->pages[] = $this->currentPage;
            $this->styleManager->setGeneralStyle($this->currentPage);
            $this->y = 800;
            $this->makeHeaderAndFooter();
        }
    }
}