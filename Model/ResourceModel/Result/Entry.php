<?php


namespace Crealoz\EasyAudit\Model\ResourceModel\Result;

use Crealoz\EasyAudit\Api\Data\EntryInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Entry extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('crealoz_easyaudit_result_entry', EntryInterface::ID);
    }
}
