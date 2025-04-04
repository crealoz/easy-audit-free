<?php


namespace Crealoz\EasyAudit\Model\ResourceModel\Result;


class Severity extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    protected function _construct()
    {
        $this->_init('crealoz_easyaudit_result_severity', \Crealoz\EasyAudit\Api\Data\SeverityInterface::ID);
    }

}
