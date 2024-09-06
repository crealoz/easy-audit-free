<?php

namespace Crealoz\EasyAudit\Model\ResourceModel\AuditRequest;

use Crealoz\EasyAudit\Model\AuditRequest;
use Crealoz\EasyAudit\Model\ResourceModel\AuditRequest as AuditRequestResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(AuditRequest::class, AuditRequestResourceModel::class);
    }
}
