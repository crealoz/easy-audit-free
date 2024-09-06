<?php

namespace Crealoz\EasyAudit\Model\ResourceModel;

use Crealoz\EasyAudit\Api\Data\AuditInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Audit extends AbstractDb
{
    protected function _construct()
    {
        $this->_init($this->getConnection()->getTableName('crealoz_easyaudit'), AuditInterface::ID);
    }
}
