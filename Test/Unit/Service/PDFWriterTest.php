<?php

namespace Crealoz\EasyAudit\Test\Unit\Service;

use Crealoz\EasyAudit\Service\PDFWriter;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter\SizeCalculation;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PDFWriterTest extends TestCase
{
    private PDFWriter $pdfWriter;
    private Filesystem $filesystem;
    private SizeCalculation $sizeCalculation;
    private LoggerInterface $logger;
    private Reader $moduleReader;
    private ModulePaths $modulePaths;
    private \ReflectionClass $reflection;
    /**
     * @var (object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject|\Zend_Pdf_Page|(\Zend_Pdf_Page&object&\PHPUnit\Framework\MockObject\MockObject)|(\Zend_Pdf_Page&\PHPUnit\Framework\MockObject\MockObject)
     */
    private $mockedPage;

    /**
     * @var PDFWriter\StyleManager $styleManager
     */
    private PDFWriter\StyleManager $styleManager;

    private $mockedStyleManager;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->sizeCalculation = $this->createMock(SizeCalculation::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->moduleReader = $this->createMock(Reader::class);
        $this->modulePaths = $this->createMock(ModulePaths::class);
        $this->styleManager = new PDFWriter\StyleManager();
        $this->BlockVsVMRatio = $this->createMock(PDFWriter\SpecificSection\BlockVMRatio::class);

        $this->mockedPage = $this->createMock(\Zend_Pdf_Page::class);
        $this->mockedStyleManager = $this->createMock(\Crealoz\EasyAudit\Service\PDFWriter\StyleManager::class);


        $this->pdfWriter = new PDFWriter(
            $this->filesystem,
            $this->sizeCalculation,
            $this->logger,
            $this->moduleReader,
            $this->modulePaths,
            $this->styleManager,
            [
                'manageBlockVMRatio' =>  $this->BlockVsVMRatio
            ],
            50,
            5
        );

        $this->reflection = new \ReflectionClass($this->pdfWriter);

        $pdfProperty = $this->reflection->getProperty('pdf');
        $pdfProperty->setAccessible(true);
        $pdfProperty->setValue($this->pdfWriter, new \Zend_Pdf());

        $this->pdfWriter->currentPage = new \Zend_Pdf_Page(100, 100);
    }

    public function testCreatedPDF(): void
    {
        $results = [
            'introduction' => [
                'overall' => [
                    'summary' => ['text' => 'This is a test summary.'],
                    'files' => ['scope' => ['file1' => 10, 'file2' => 5]]
                ]
            ],
            'PHP' => [
                'BlockVsViewModelRatio' => [
                    'hasErrors' => true,
                    'title' => 'Subsection 1',
                    'errors' => [
                        'testError' => [
                            'title' => 'Error 1',
                            'explanation' => 'Explanation 1',
                            'files' => ['file1' => 10, 'file2' => 5],
                            'specificSections' => 'manageBlockVMRatio'
                        ]
                    ],
                    'warnings' => [
                        'testWarning' => [
                            'title' => 'Warning 1',
                            'explanation' => 'Explanation 1',
                            'files' => ['file1' => 10, 'file2' => 5]
                        ]
                    ],
                    'suggestions' => [
                        'testSuggestion' => [
                            'title' => 'Suggestion 1',
                            'explanation' => 'Explanation 1',
                            'files' => ['file1' => 10, 'file2' => 5]
                        ]
                    ]
                ]
            ]
        ];

        $filename = 'test';

        $tempDir = sys_get_temp_dir() . '/pdf_test';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $this->moduleReader->method('getModuleDir')->willReturn($tempDir);
        $directoryRead = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $directoryWrite = $this->createMock(\Magento\Framework\Filesystem\Directory\WriteInterface::class);

        $directoryRead->method('isExist')->willReturn(true);
        $directoryWrite->method('getAbsolutePath')->willReturn($tempDir . '/' . $filename . '.pdf');

        $this->filesystem->method('getDirectoryRead')->willReturn($directoryRead);
        $this->filesystem->method('getDirectoryWrite')->willReturn($directoryWrite);

        $filePath = $this->pdfWriter->createdPDF($results, $filename);

        $this->assertStringContainsString($filename, $filePath, 'Filename should be part of the resulting file path');
        $this->assertFileExists($tempDir . '/' . $filename . '.pdf', 'File should not actually exist since save does nothing');

        unlink($filePath);
        rmdir($tempDir);
    }

    public function testDelegateToAnnex()
    {
        $files = array_fill(0, 200, 'file');
        $description = 'Test Annex';

        $this->pdfWriter->delegateToAnnex(2, $files, $description);

        $annexesProperty = $this->reflection->getProperty('annexes');
        $annexesProperty->setAccessible(true);
        $annexes = $annexesProperty->getValue($this->pdfWriter);
        $this->assertArrayHasKey(1, $annexes, 'The annex should be added.');
        $this->assertSame($files, $annexes[1]['files'], 'Files should match the provided input.');
        $this->assertSame($description, $annexes[1]['description'], 'Description should match the provided input.');
    }



    /**
     * Next tests are for the writeLine method. This method is used to write text on the current page and it calculates
     * the amount of line depending on available width.
     * For an A4 page, the width is 595 and the height is 842. If columns are used, the width is divided by the number of columns.
     * We also subtract left and right margins from the width (50 points).
     * The available width is then 495/5 = 99. A letter is in average 10 points wide. This means that we can fit 9 letters in a line per column.
     */

    public function testWriteLineWithShortText(): void
    {
        $this->mockedPage
            ->expects($this->once())
            ->method('drawText')
            ->with($this->equalTo("Short text"), $this->anything(), $this->anything());

        $this->pdfWriter->currentPage = $this->mockedPage;

        $this->pdfWriter->writeLine("Short text");
    }

    public function testWriteLineWithLongText(): void
    {
        $longText = str_repeat("This is a long line. ", 10);

        $this->mockedPage
            ->expects($this->atLeast(2)) // Expecting split into multiple lines
            ->method('drawText')
            ->with($this->anything(), $this->anything(), $this->anything());

        $this->pdfWriter->currentPage = $this->mockedPage;


        $this->pdfWriter->writeLine($longText);
    }

    public function testWriteLineWithFilePath(): void
    {
        $filePath = "/var/www/html/app/code/Crealoz/EasyAudit/Service/PDFWriter.php";

        $this->mockedPage
            ->expects($this->atLeastOnce())
            ->method('drawText')
            ->with($this->anything(), $this->anything(), $this->anything());

        $this->pdfWriter->currentPage = $this->mockedPage;


        $this->pdfWriter->writeLine($filePath, true);
    }

    protected function tearDown(): void
    {
        unset($this->pdfWriter);
        unset($this->filesystem);
        unset($this->sizeCalculation);
        unset($this->logger);
        unset($this->moduleReader);
        unset($this->modulePaths);
        unset($this->styleManager);
        unset($this->BlockVsVMRatio);
        unset($this->mockedPage);
        unset($this->mockedStyleManager);

    }

}