<?php

namespace Crealoz\EasyAudit\Test\Unit\Controller\Adminhtml\Request;

use PHPUnit\Framework\TestCase;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Crealoz\EasyAudit\Api\FileRepositoryInterface;
use Crealoz\EasyAudit\Controller\Adminhtml\Request\Download;

class DownloadTest extends TestCase
{
    private $downloadController;
    private $contextMock;
    private $filesystemMock;
    private $driverMock;
    private $fileRepositoryMock;
    private $ioFileMock;

    protected function setUp(): void
    {

        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->requestMock->method('getParam')
            ->with('request_id')
            ->willReturnOnConsecutiveCalls(123, 456, 789, null);

        $this->responseMock = $this->createMock(\Magento\Framework\HTTP\PhpEnvironment\Response::class);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->method('getMessageManager')->willReturn($this->createMock(\Magento\Framework\Message\ManagerInterface::class));

        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->driverMock = $this->createMock(DriverInterface::class);
        $this->ioFileMock = $this->createMock(File::class);
        $this->fileRepositoryMock = $this->createMock(FileRepositoryInterface::class);
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'easy_audit_test';
        mkdir($this->tempDir);

        $directoryReadMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);

        $directoryReadMock->method('isExist')
            ->willReturn(true);

        $tempDir = $this->tempDir;
        $directoryReadMock->method('getAbsolutePath')
            ->willReturnCallback(function ($filename) use ($tempDir) {
                if (preg_match('/\.zip$/', $filename)) {
                    return $tempDir . '/audit_789.zip'; // Retourne le chemin générique pour les fichiers ZIP
                }
                return match ($filename) {
                    'file1' => $tempDir . '/file1.pdf',
                    'file2' => $tempDir . '/file2.pdf',
                    default => '',
                };
            });
        $file1 = $this->tempDir . DIRECTORY_SEPARATOR . 'file1.pdf';
        $file2 = $this->tempDir . DIRECTORY_SEPARATOR . 'file2.pdf';
        file_put_contents($file1, 'dummy content for file1');
        file_put_contents($file2, 'dummy content for file2');

        $fileMock1 = $this->createMock(\Crealoz\EasyAudit\Api\Data\FileInterface::class);
        $fileMock1->method('getFileName')->willReturn('file1');
        $fileMock2 = $this->createMock(\Crealoz\EasyAudit\Api\Data\FileInterface::class);
        $fileMock2->method('getFileName')->willReturn('file2');
        $this->ioFileMock->method('getPathInfo')->willReturnCallback(
            function ($filename) {
                return [
                    'dirname' => $this->tempDir,
                    'basename' => $filename . '.pdf',
                    'filename' => $filename,
                    'extension' => 'pdf'
                ];
            }
        );
        $this->fileRepositoryMock->method('getByRequestId')->willReturnCallback(
            function ($requestId) use ($fileMock1, $fileMock2) {
                if ($requestId === 123) {
                    return [$fileMock1];
                }
                if ($requestId === 789) {
                    return [$fileMock1, $fileMock2];
                }
                return [];
            }
        );

        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryReadMock);

        $this->driverMock->method('fileGetContents')->willReturnCallback(
            function ($filename) {
                return file_get_contents($filename);
            }
        );

        $this->downloadController = new Download(
            $this->contextMock,
            $this->filesystemMock,
            $this->driverMock,
            $this->ioFileMock,
            $this->fileRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("$this->tempDir/*.*"));
        rmdir($this->tempDir);
        unset($this->downloadController);
        unset($this->contextMock);
        unset($this->filesystemMock);
        unset($this->driverMock);
        unset($this->fileRepositoryMock);
        unset($this->ioFileMock);
        unset($this->tempDir);
    }

    public function testExecute()
    {
        // Tells that setBody() method is called twice
        $this->responseMock->expects($this->exactly(2))->method('setBody');
        $this->downloadController->execute();
        $this->childTestExecuteWithSpy();
        $this->downloadController->execute();
        $this->childTestExecuteWithSpy();
    }

    /**
     * @runInSeparateProcess
     */
    public function childTestExecuteWithSpy()
    {
        $redirectSpy = $this->getMockBuilder(get_class($this->downloadController))
            ->setConstructorArgs([
                $this->contextMock,
                $this->filesystemMock,
                $this->driverMock,
                $this->ioFileMock,
                $this->fileRepositoryMock
            ])
            ->onlyMethods(['_redirect'])
            ->getMock();

        $redirectSpy->expects($this->once())
            ->method('_redirect')
            ->with('*/*/index');

        $redirectSpy->execute();
    }
}
