<?php

namespace Crealoz\EasyAudit\Model\ResourceModel\Audit;

use Crealoz\EasyAudit\Model\Audit;
use Crealoz\EasyAudit\Model\ResourceModel\Audit as AuditResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Audit::class, AuditResourceModel::class);
    }
}
