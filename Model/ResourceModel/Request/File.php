<?php

namespace Crealoz\EasyAudit\Model\ResourceModel\Request;


class File  extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{



    protected function _construct() {
        $this->_init('crealoz_easyaudit_request_file', \Crealoz\EasyAudit\Api\Data\FileInterface::ID);
    }

}
