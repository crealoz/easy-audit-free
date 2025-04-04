<?php


namespace Crealoz\EasyAudit\Model\ResourceModel\Result;


class Type extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    protected function _construct()
    {
        $this->_init('crealoz_easyaudit_result_type', \Crealoz\EasyAudit\Api\Data\TypeInterface::ID);
    }

}
