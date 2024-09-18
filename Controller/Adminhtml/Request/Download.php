<?php

namespace Crealoz\EasyAudit\Controller\Adminhtml\Request;

use Crealoz\EasyAudit\Service\PDFWriter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

class Download extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        private readonly \Magento\Framework\Filesystem $filesystem,
        private readonly DriverInterface $driver
    ) {
        parent::__construct($context);
    }

    /**
     * gets the parameter filename from url and serves the file for download
     */
    public function execute()
    {
        $filename = $this->getRequest()->getParam('filename');

        if (!$this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist(PDFWriter::MEDIA_FOLDER . DIRECTORY_SEPARATOR . $filename . '.pdf')) {
            $this->messageManager->addErrorMessage(__('File not found'));
            return $this->_redirect('*/*/index');
        }

        $filePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(PDFWriter::MEDIA_FOLDER . DIRECTORY_SEPARATOR . $filename . '.pdf');

        $this->getResponse()->setHeader('Content-Type', 'application/pdf');
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $filename . '.pdf');
        try {
            $this->getResponse()->setBody($this->driver->fileGetContents($filePath));
        } catch (FileSystemException $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while downloading the file.'));
            return $this->_redirect('*/*/index');
        }
    }
}