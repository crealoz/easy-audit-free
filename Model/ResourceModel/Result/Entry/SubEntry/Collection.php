<?php


namespace Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry;


class Collection  extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{



    protected function _construct() {
        $this->_init(\Crealoz\EasyAudit\Model\Result\Entry\SubEntry::class, \Crealoz\EasyAudit\Model\ResourceModel\Result\Entry\SubEntry::class);
    }

}
