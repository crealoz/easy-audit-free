<?php


namespace Crealoz\EasyAudit\Model\Result\Entry;


class SubEntry  extends \Magento\Framework\Model\AbstractModel implements \Crealoz\EasyAudit\Api\Data\SubEntryInterface
{

    protected $_idFieldName = self::ID;



    public function getSubentryId() {
        return $this->getData(self::ID);
    }

    public function setSubentryId(int $subentry_id) {
        $this->setData(self::ID, $subentry_id);
    }

    public function getEntryId() {
        return $this->getData(self::ENTRY_ID);
    }

    public function setEntryId(int $entry_id) {
        $this->setData(self::ENTRY_ID, $entry_id);
    }

    public function getSubentry() {
        return $this->getData(self::SUBENTRY);
    }

    public function setSubentry(string $subentry) {
        $this->setData(self::SUBENTRY, $subentry);
    }

}
