<?php

namespace Crealoz\EasyAudit\Test\Unit\Processors\Results;

use Crealoz\EasyAudit\Processor\Results\ErroneousFiles;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
class ErroneousFilesTest extends \PHPUnit\Framework\TestCase
{

    private $modulePaths;
    private $erroneousFiles;
    protected function setUp(): void
    {
        $this->modulePaths = $this->createMock(ModulePaths::class);
        $this->erroneousFiles = new ErroneousFiles($this->modulePaths);
    }

    public function testProcessResultsWithScoresHigherThan10()
    {
        $results = [
            'erroneousFiles' => [
                'app/code/Provider/Module/file1.php' => 12,
                'app/code/Provider/Module/file2.php' => 5,
                'vendor/provider/module/file3.php' => 10,
                'app/design/frontend/Provider/Theme/file4.php' => 3,
                'ModuleName' => 2
            ]
        ];

        $this->modulePaths->method('stripVendorOrApp')
            ->willReturnCallback(function ($file) {
                return basename($file);
            });

        $processedResults = $this->erroneousFiles->processResults($results);

        $this->assertCount(1, $processedResults['introduction']);
        $this->assertStringContainsString('2 files have a score equal to or higher than 10', $processedResults['introduction'][0]['summary'][0]);
        $this->assertStringContainsString('1 files have a score equal to or higher than 5', $processedResults['introduction'][0]['summary'][1]);
    }
}