<?php


namespace Crealoz\EasyAudit\Model\ResourceModel\Result\Severity;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{


    protected function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\Result\Severity::class, \Crealoz\EasyAudit\Model\ResourceModel\Result\Severity::class);
    }

}
