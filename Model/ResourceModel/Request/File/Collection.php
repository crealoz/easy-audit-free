<?php

namespace Crealoz\EasyAudit\Model\ResourceModel\Request\File;


class Collection  extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{



    protected function _construct() {
        $this->_init(\Crealoz\EasyAudit\Model\Request\File::class, \Crealoz\EasyAudit\Model\ResourceModel\Request\File::class);
    }

}
