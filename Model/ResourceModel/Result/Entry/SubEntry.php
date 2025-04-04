<?php


namespace Crealoz\EasyAudit\Model\ResourceModel\Result\Entry;


class SubEntry  extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{



    protected function _construct() {
        $this->_init('crealoz_easyaudit_result_subentry', \Crealoz\EasyAudit\Api\Data\SubEntryInterface::ID);
    }

}
