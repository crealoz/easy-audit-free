<?php

namespace Crealoz\EasyAudit\Controller\Adminhtml\Request;

use Crealoz\EasyAudit\Api\Data\FileInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

class Download extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Filesystem
     * @readonly
     */
    private \Magento\Framework\Filesystem $filesystem;
    /**
     * @var DriverInterface
     * @readonly
     */
    private DriverInterface $driver;
    /**
     * @readonly
     */
    private \Magento\Framework\Filesystem\Io\File $ioFile;
    /**
     * @var \Crealoz\EasyAudit\Api\FileRepositoryInterface
     * @readonly
     */
    private \Crealoz\EasyAudit\Api\FileRepositoryInterface $fileRepository;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param DriverInterface $driver
     * @param \Crealoz\EasyAudit\Api\FileRepositoryInterface $fileRepository
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Filesystem $filesystem, DriverInterface $driver, \Magento\Framework\Filesystem\Io\File $ioFile, \Crealoz\EasyAudit\Api\FileRepositoryInterface $fileRepository)
    {
        $this->filesystem = $filesystem;
        $this->driver = $driver;
        $this->ioFile = $ioFile;
        $this->fileRepository = $fileRepository;
        parent::__construct($context);
    }
    /**
     * gets the parameter filename from url and serves the file for download
     */
    public function execute()
    {
        $auditRequestId = $this->getRequest()->getParam('request_id');

        if (!$auditRequestId) {
            $this->messageManager->addErrorMessage(__('Invalid request ID'));
            return $this->_redirect('*/*/index');
        }

        $files = $this->fileRepository->getByRequestId($auditRequestId);

        if (count($files) == 0) {
            $this->messageManager->addErrorMessage(__('No files found for this request'));
            return $this->_redirect('*/*/index');
        }

        try {
            if (count($files) > 1) {
                $fileData = $this->prepareZip($files);
                $filePath = $fileData['filepath'];
                $filename = $fileData['filename'];
                $this->getResponse()->setHeader('Content-Type', 'application/zip');
                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $filename);
            } else {
                $file = array_shift($files);
                $filePath = $this->getFilePath($file);
                $filename = $this->getFileName($file) . '.pdf';
                $this->getResponse()->setHeader('Content-Type', 'application/pdf');
                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $filename . '.pdf');
            }
            $this->getResponse()->setBody($this->driver->fileGetContents($filePath));
            // Unlink the zip file if it was created
            if (count($files) > 1) {
                $this->driver->deleteFile($filePath);
            }
        } catch (FileSystemException $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while downloading the file.'));
            return $this->_redirect('*/*/index');
        }
    }

    /**
     * @param FileInterface $file
     * @return string
     * @throws FileSystemException
     */
    private function getFilePath(FileInterface $file)
    {
        $filename = $file->getFilename();
        if (!$this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist($filename)) {
            throw new FileSystemException(__('File not found'));
        }

        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($filename);
    }

    private function getFileName(FileInterface $file)
    {
        $fileInfo = $this->ioFile->getPathInfo($file->getFilename());
        return $fileInfo['filename'];
    }

    /**
     * @param $files
     * @return array
     * @throws FileSystemException
     */
    private function prepareZip($files)
    {
        $zip = new \ZipArchive();
        $zipFileName = uniqid('audit_files_') . '.zip';
        $zipFilePath = $this->filesystem->getDirectoryRead(DirectoryList::TMP)->getAbsolutePath($zipFileName);
        if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== true) {
            throw new FileSystemException(__('Could not create zip file'));
        }
        foreach ($files as $file) {
            $filepath = $this->getFilePath($file);
            $filename = $this->getFileName($file);
            $zip->addFile($filepath, $filename . '.pdf');
        }
        $zip->close();
        return ['filepath' => $zipFilePath, 'filename' => $zipFileName];
    }
}