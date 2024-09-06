<?php

namespace Crealoz\EasyAudit\Model\ResourceModel;

use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AuditRequest extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('crealoz_easyaudit_request', AuditRequestInterface::ID);
    }
}
