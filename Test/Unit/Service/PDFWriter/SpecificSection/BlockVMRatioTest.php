<?php

namespace Crealoz\EasyAudit\Test\Unit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter;
use Crealoz\EasyAudit\Service\PDFWriter\SizeCalculation;
use Crealoz\EasyAudit\Service\PDFWriter\SpecificSection\BlockVMRatio;
use PHPUnit\Framework\TestCase;

class BlockVMRatioTest extends TestCase
{
    private $sizeCalculationMock;
    private $pdfWriterMock;
    private $blockVMRatio;

    protected function setUp(): void
    {
        $this->sizeCalculationMock = $this->createMock(SizeCalculation::class);
        $this->pdfWriterMock = $this->createMock(PDFWriter::class);

        $this->blockVMRatio = new BlockVMRatio($this->sizeCalculationMock);
    }

    public function testWriteContent()
    {
        $subresults = [
            'files' => [
                'Module1' => '0.5',
                'Module2' => '0.8'
            ]
        ];

        $this->pdfWriterMock->expects($this->exactly(3))
            ->method('writeLine')
            ->willReturnOnConsecutiveCalls(
                [$this->equalTo(__('Modules:'))],
                [$this->equalTo(__('-%1(ratio : %2)', 'Module1', '0.5'))],
                [$this->equalTo(__('-%1(ratio : %2)', 'Module2', '0.8'))]
            );

        // make writeContent method accessible
        $reflection = new \ReflectionClass(BlockVMRatio::class);
        $method = $reflection->getMethod('writeContent');
        $method->setAccessible(true);

        $method->invokeArgs($this->blockVMRatio, [$this->pdfWriterMock, $subresults]);
    }

    public function testCalculateSize()
    {
        $subresults = [
            'files' => [
                'Module1' => '0.5',
                'Module2' => '0.8'
            ]
        ];

        $this->sizeCalculationMock->method('calculateTitlePlusFirstSubsectionSize')
            ->with([$subresults])
            ->willReturn(10);

        $this->sizeCalculationMock->method('getSizeForText')
            ->willReturn(25);

        $result = $this->blockVMRatio->calculateSize($subresults);

        $this->assertEquals(85, $result);
    }

    public function testGetLine()
    {
        $result = $this->blockVMRatio->getLine('Module1', '0.5');
        $this->assertEquals(__('-%1(ratio : %2)', 'Module1', '0.5'), $result);
    }

    public function testGetPHPFormatedText()
    {
        $subResults = [
            'Module1' => '0.5',
            'Module2' => '0.8'
        ];

        $expectedText = __('Modules') . PHP_EOL;
        $expectedText .= __('-%1(ratio : %2)', 'Module1', '0.5') . PHP_EOL;
        $expectedText .= __('-%1(ratio : %2)', 'Module2', '0.8') . PHP_EOL;

        $result = $this->blockVMRatio->getPHPFormatedText('Modules', $subResults);

        $this->assertEquals($expectedText, $result);
    }
}