<?php

namespace Crealoz\EasyAudit\Model\ResourceModel;

use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Crealoz\EasyAudit\Model\Request\FileFactory;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AuditRequest extends AbstractDb
{
    /**
     * @readonly
     */
    protected FileFactory $fileFactory;
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        FileFactory $fileFactory,
        $connectionName = null
    )
    {
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('crealoz_easyaudit_request', AuditRequestInterface::ID);
    }

    /**
     * Hydrates the model with files attached to the object
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterLoad($object);
        if (!$object instanceof AuditRequestInterface) {
            return $this;
        }
        $filesFromDb = $this->getConnection()->fetchAll(
            $this->getConnection()->select()
                ->from($this->getTable('crealoz_easyaudit_request_file'))
                ->where('request_id = ?', $object->getId())
        );
        foreach ($filesFromDb as $file) {
            $object->addFile($this->fileFactory->create()->setData($file));
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object instanceof AuditRequestInterface) {
            return $this;
        }
        $files = $object->getFiles();
        if (count($files) > 0) {
            $connection = $this->getConnection();
            $connection->delete(
                $this->getTable('crealoz_easyaudit_request_file'),
                ['request_id = ?' => $object->getId()]
            );
            foreach ($files as $file) {
                /** @var \Crealoz\EasyAudit\Api\Data\FileInterface $file */
                $connection->insert(
                    $this->getTable('crealoz_easyaudit_request_file'),
                    [
                        'request_id' => $object->getId(),
                        'file_id' => $file->getId(),
                        'filename' => $file->getFileName(),
                        'content' => $file->getContent(),
                    ]
                );
            }
        }
        return parent::_afterSave($object);
    }
}
