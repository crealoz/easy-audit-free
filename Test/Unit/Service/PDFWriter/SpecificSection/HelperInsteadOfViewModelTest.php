<?php
namespace Crealoz\EasyAudit\Test\Unit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter\SpecificSection\HelperInsteadOfViewModel;
use Crealoz\EasyAudit\Service\PDFWriter\SizeCalculation;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter\StyleManager;
use PHPUnit\Framework\TestCase;

class HelperInsteadOfViewModelTest extends TestCase
{
    private HelperInsteadOfViewModel $helper;
    private SizeCalculation $sizeCalculation;
    private ModulePaths $modulePaths;
    private StyleManager $styleManager;

    protected function setUp(): void
    {
        $this->sizeCalculation = $this->createMock(SizeCalculation::class);
        $this->modulePaths = $this->createMock(ModulePaths::class);
        $this->styleManager = $this->createMock(StyleManager::class);

        $this->helper = new HelperInsteadOfViewModel(
            $this->sizeCalculation,
            $this->modulePaths,
            $this->styleManager
        );
    }

    public function testCalculateSize(): void
    {
        $subresults = [
            'files' => [
                'file1' => ['usageCount' => 2, 'template1' => 1, 'template2' => 1],
                'file2' => ['usageCount' => 3, 'template3' => 2]
            ]
        ];

        $this->sizeCalculation->method('calculateTitlePlusFirstSubsectionSize')->willReturn(10);
        $this->sizeCalculation->method('getSizeForText')->willReturn(5);
        $this->sizeCalculation->method('calculateMultidimensionalArraySize')->willReturn(20);

        $size = $this->helper->calculateSize($subresults);

        $this->assertEquals(35, $size);
    }

    public function testGetLine(): void
    {
        $key = 'file1';
        $entry = ['usageCount' => 2, 'template1' => 1, 'template2' => 1];

        $this->modulePaths->method('stripVendorOrApp')->willReturnArgument(0);

        $line = $this->helper->getLine($key, $entry);

        $expected = "- file1 (usages: 2)\n  -template1(1)\n  -template2(1)";
        $this->assertEquals($expected, $line);
    }

    public function testGetPHPFormatedText(): void
    {
        $subResults = [
            'file1' => ['usageCount' => 2, 'template1' => 1, 'template2' => 1],
            'file2' => ['usageCount' => 3, 'template3' => 2]
        ];

        $this->modulePaths->method('stripVendorOrApp')->willReturnArgument(0);

        $text = $this->helper->getPHPFormatedText('key', $subResults);

        $expected = "Files\n- file1 (usages: 2)\n  -template1(1)\n  -template2(1)\n- file2 (usages: 3)\n  -template3(2)\n";
        $this->assertEquals($expected, $text);
    }
}