<?php


namespace Crealoz\EasyAudit\Model\Result;

use Crealoz\EasyAudit\Api\Data\EntryInterface;
use Crealoz\EasyAudit\Api\Data\ResultInterface;
use Crealoz\EasyAudit\Api\Data\SubEntryInterface;
use Crealoz\EasyAudit\Api\Data\TypeInterface;
use Crealoz\EasyAudit\Model\SubEntryRepository;
use Magento\Framework\Model\AbstractModel;

class Entry extends AbstractModel implements EntryInterface
{
    protected $_idFieldName = self::ID;

    protected array $subEntries = [];

    protected bool $subEntriesLoaded = false;

    protected function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\ResourceModel\Result\Entry::class);
    }

    /**
     * @return int
     */
    public function getEntryId(): int
    {
        return $this->getData(self::ID);
    }

    /**
     * @param int $entry_id
     * @return self
     */
    public function setEntryId(int $entry_id): self
    {
        $this->setData(self::ID, $entry_id);
        return $this;
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return $this->getData(self::TYPE_ID);
    }

    /**
     * @param int $type_id
     * @return self
     */
    public function setTypeId(int $type_id): self
    {
        $this->setData(self::TYPE_ID, $type_id);
        return $this;
    }

    /**
     * @return int
     */
    public function getResultId(): int
    {
        return $this->getData(self::RESULT_ID);
    }

    /**
     * @param int $result_id
     * @return self
     */
    public function setResultId(int $result_id): self
    {
        $this->setData(self::RESULT_ID, $result_id);
        return $this;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @param int $parent_id
     * @return self
     */
    public function setParentId(int $parent_id): self
    {
        $this->setData(self::PARENT_ID, $parent_id);
        return $this;
    }

    /**
     * @return string
     */
    public function getEntry(): string
    {
        return $this->getData(self::ENTRY);
    }

    /**
     * @param string $entry
     * @return self
     */
    public function setEntry(string $entry): self
    {
        $this->setData(self::ENTRY, $entry);
        return $this;
    }

    public function getType(): TypeInterface
    {
        return $this->getData(self::TYPE);
    }

    public function setType(TypeInterface $type): EntryInterface
    {
        $this->setData(self::TYPE, $type);
        return $this;
    }

    public function getResult(): ResultInterface
    {
        return $this->getData(self::RESULT);
    }

    public function setResult(ResultInterface $result): EntryInterface
    {
        $this->setData(self::RESULT, $result);
        return $this;
    }

    public function getSubEntries(): array
    {
        return $this->getData(self::SUB_ENTRIES) ?? [];
    }

    public function setSubEntries(array $subEntries): EntryInterface
    {
        $this->setData(self::SUB_ENTRIES, $subEntries);
        return $this;
    }

    public function addSubEntry(SubEntryInterface $subEntry): EntryInterface
    {
        $subEntries = $this->getSubEntries();
        $subEntries[] = $subEntry;
        $this->setSubEntries($subEntries);
        return $this;
    }
}
