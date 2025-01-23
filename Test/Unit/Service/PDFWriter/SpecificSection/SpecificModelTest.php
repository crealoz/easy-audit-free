<?php

namespace Crealoz\EasyAudit\Test\Unit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\PDFWriter;
use Crealoz\EasyAudit\Service\PDFWriter\SizeCalculation;
use Crealoz\EasyAudit\Service\PDFWriter\SpecificSection\SpecificModel;
use PHPUnit\Framework\TestCase;

class SpecificModelTest extends TestCase
{
    private $sizeCalculation;
    private $pdfWriterMock;
    private $modulePathsMock;
    private $specificModel;

    protected function setUp(): void
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->sizeCalculation = new SizeCalculation($logger);
        $this->pdfWriterMock = $this->createMock(PDFWriter::class);
        $this->modulePathsMock = $this->createMock(ModulePaths::class);

        $this->specificModel = new SpecificModel(
            $this->sizeCalculation,
            $this->modulePathsMock
        );
    }

    public function testWriteContent()
    {
        $subresults = [
            'files' => [
                'Module1' => ['Argument1', 'Argument2'],
                'Module2' => ['Argument3']
            ]
        ];

        $this->pdfWriterMock->expects($this->exactly(6))
            ->method('writeLine')
            ->willReturnOnConsecutiveCalls(
                ['Files:'],
                ['Module1', true],
                ['-Argument1', true, 8],
                ['-Argument2', true, 8],
                ['Module2', true],
                ['-Argument3', true, 8]
            );

        // make writeContent method accessible
        $reflection = new \ReflectionClass(SpecificModel::class);
        $method = $reflection->getMethod('writeContent');
        $method->setAccessible(true);

        $method->invokeArgs($this->specificModel, [$this->pdfWriterMock, $subresults]);
    }

    public function testCalculateSize()
    {
        $subresults = [
            'title' => 'Files',
            'explanation' => 'It contains the files and their arguments and should be a bit long so we can test the size
             calculation correctly and make sure it is working as expected',
            'files' => [
                'Module1' => ['Argument1', 'Argument2'],
                'Module2' => ['Argument3']
            ]
        ];

        $result = $this->specificModel->calculateSize($subresults);

        $this->assertEquals(174, $result);
    }

    public function testGetLine()
    {
        $result = $this->specificModel->getLine('Module1', ['Argument1', 'Argument2']);
        $expected = "-Module1\n  -Argument1\n  -Argument2\n";
        $this->assertEquals($expected, $result);

        $result = $this->specificModel->getLine('Module1', 'Argument1');
        $expected = "-Argument1";
        $this->assertEquals($expected, $result);
    }

    public function testGetPHPFormatedText()
    {
        $subResults = [
            'Module1' => ['Argument1', 'Argument2'],
            'Module2' => ['Argument3']
        ];

        $expectedText = __('Files') . PHP_EOL;
        $expectedText .= "-Module1\n  -Argument1\n  -Argument2\n\n";
        $expectedText .= "-Module2\n  -Argument3\n\n";

        $result = $this->specificModel->getPHPFormatedText('Files', $subResults);

        $this->assertEquals($expectedText, $result);
    }
}