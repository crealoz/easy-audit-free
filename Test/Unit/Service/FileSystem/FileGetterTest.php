<?php

namespace Crealoz\EasyAudit\Test\Unit\Service\FileSystem;

use Crealoz\EasyAudit\Service\FileSystem\FileGetter;
use Crealoz\EasyAudit\Service\FileSystem\FilterGetter;
use Magento\Framework\App\Filesystem\DirectoryList;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class FileGetterTest extends TestCase
{
    private $filterGetter;
    private $directoryList;
    private $fileGetter;
    private $tempDir;

    protected function setUp(): void
    {
        $this->filterGetter = $this->createMock(FilterGetter::class);
        $this->directoryList = $this->createMock(DirectoryList::class);

        // Create a temporary directory for testing
        $this->tempDir = sys_get_temp_dir() . '/magento';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        $this->directoryList->method('getRoot')->willReturn($this->tempDir);

        if (!is_dir($this->tempDir . '/path/to/directory')) {
            mkdir($this->tempDir . '/path/to/directory', 0777, true);
        }

        $this->fileGetter = new FileGetter(
            'path/to/directory',
            '/\.php$/',
            $this->filterGetter,
            $this->directoryList,
            'filter'
        );
    }

    protected function tearDown(): void
    {
        // Clean up the temporary directory after tests
        $this->rrmdir($this->tempDir);
    }

    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    (filetype($dir."/".$object) == "dir") ? $this->rrmdir($dir."/".$object) : unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

    public function testExecuteWithoutFilter()
    {
        $this->filterGetter->method('getFilter')->willReturn([]);

        $files = $this->fileGetter->execute();

        $this->assertIsArray($files);
    }

    public function testExecuteWithFilter()
    {
        $this->filterGetter->method('getFilter')->willReturn(['ignoredFolder']);

        $files = $this->fileGetter->execute();

        $this->assertIsArray($files);
    }

    public function testApplyFilter()
    {
        $regex = $this->createMock(\RegexIterator::class);
        $regex->method('current')->willReturn([$this->tempDir . '/file.php']);
        $regex->method('valid')->willReturnOnConsecutiveCalls(true, false);

        $this->filterGetter->method('getFilter')->willReturn(['ignoredFolder']);

        $reflection = new \ReflectionClass($this->fileGetter);
        $method = $reflection->getMethod('applyFilter');
        $method->setAccessible(true);

        $files = $method->invokeArgs($this->fileGetter, [$regex]);

        $this->assertIsArray($files);
    }
}