<?php


namespace Crealoz\EasyAudit\Model\ResourceModel\Result\Type;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{


    protected function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\Result\Type::class, \Crealoz\EasyAudit\Model\ResourceModel\Result\Type::class);
    }

}
