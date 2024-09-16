<?php

namespace Crealoz\EasyAudit\Controller\Adminhtml\Request;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        private readonly \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
    }

    /**
     * gets the parameter filename from url and serves the file for download
     */
    public function execute()
    {
        $filename = $this->getRequest()->getParam('filename');

        if (!$this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist('/crealoz/' . $filename . '.pdf')) {
            $this->messageManager->addErrorMessage(__('File not found'));
            return $this->_redirect('*/*/index');
        }

        $filePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/crealoz/' . $filename . '.pdf');

        $this->getResponse()->setHeader('Content-Type', 'application/pdf');
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . basename($filePath));
        $this->getResponse()->setBody(file_get_contents($filePath));
    }
}