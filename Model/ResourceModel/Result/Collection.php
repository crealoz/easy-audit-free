<?php


namespace Crealoz\EasyAudit\Model\ResourceModel\Result;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{


    protected function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\Result::class, \Crealoz\EasyAudit\Model\ResourceModel\Result::class);
    }

}
