<?php


namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\Data\EntryInterface;
use Crealoz\EasyAudit\Api\Data\ResultInterface;
use Magento\Framework\Model\AbstractModel;

class Result extends AbstractModel implements ResultInterface
{
    protected $_idFieldName = self::ID;

    public function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\ResourceModel\Result::class);
    }

    /**
     * @return int
     */
    public function getResultId(): int
    {
        return $this->getData(self::ID);
    }

    /**
     * @param int $result_id
     * @return self
     */
    public function setResultId(int $result_id): self
    {
        $this->setData(self::ID, $result_id);
        return $this;
    }

    /**
     * @return int
     */
    public function getRequestId(): int
    {
        return $this->getData(self::REQUEST_ID);
    }

    /**
     * @param int $request_id
     * @return self
     */
    public function setRequestId(int $request_id): self
    {
        $this->setData(self::REQUEST_ID, $request_id);
        return $this;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->getData(self::SUMMARY);
    }

    /**
     * @param string $summary
     * @return self
     */
    public function setSummary(string $summary): self
    {
        $this->setData(self::SUMMARY, $summary);
        return $this;
    }


    /**
     * @return \Crealoz\EasyAudit\Api\Data\SeverityInterface
     */
    public function getSeverity(): \Crealoz\EasyAudit\Api\Data\SeverityInterface
    {
        return $this->getData(self::SEVERITY);
    }

    /**
     * @param \Crealoz\EasyAudit\Api\Data\SeverityInterface $severity
     * @return self
     */
    public function setSeverity(\Crealoz\EasyAudit\Api\Data\SeverityInterface $severity): self
    {
        $this->setData(self::SEVERITY, $severity);
        return $this;
    }

    /**
     * @return array
     */
    public function getEntries(): array
    {
        return $this->getData(self::ENTRIES) ?? [];
    }

    /**
     * @param array $entries
     * @return self
     */
    public function setEntries(array $entries): self
    {
        $this->setData(self::ENTRIES, $entries);
        return $this;
    }

    /**
     * @return string
     */
    public function getProcessor(): string
    {
        return $this->getData(self::PROCESSOR);
    }

    /**
     * @param string $group
     * @return self
     */
    public function setProcessor(string $group): self
    {
        $this->setData(self::PROCESSOR, $group);
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->setData(self::TITLE, $title);
        return $this;
    }

    /**
     * @return int
     */
    public function getSeverityId(): int
    {
        return $this->getData(self::SEVERITY_ID);
    }

    /**
     * @param int $severity_id
     * @return self
     */
    public function setSeverityId(int $severity_id): self
    {
        $this->setData(self::SEVERITY_ID, $severity_id);
        return $this;
    }

    /**
     * @param EntryInterface $entry
     * @return self
     */
    public function addEntry(EntryInterface $entry): self
    {
        $entries = $this->getEntries();
        $entries[] = $entry;
        $this->setEntries($entries);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrEnabled(): int
    {
        return $this->getData(self::PR_ENABLED) ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function setPrEnabled(int $pr_enabled): self
    {
        $this->setData(self::PR_ENABLED, $pr_enabled);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrStatus(): ?string
    {
        return $this->getData(self::PR_STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setPrStatus(string $pr_status): self
    {
        $this->setData(self::PR_STATUS, $pr_status);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiff(): ?string
    {
        return $this->getData(self::DIFF);
    }

    /**
     * @inheritdoc
     */
    public function setDiff(string $diff): self
    {
        $this->setData(self::DIFF, $diff);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQueueId(): ?string
    {
        return $this->getData(self::QUEUE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQueueId(string $queue_id): self
    {
        $this->setData(self::QUEUE_ID, $queue_id);
        return $this;
    }
}
