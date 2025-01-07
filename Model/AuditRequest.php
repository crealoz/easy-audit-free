<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Crealoz\EasyAudit\Api\Data\FileInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class AuditRequest
 * @package Crealoz\EasyAudit\Model
 * @api
 */
class AuditRequest extends AbstractModel implements AuditRequestInterface
{
    protected $_idFieldName = self::ID;

    protected function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest::class);
    }

    /**
     * @inheritdoc
     */
    public function getUsername(): ?string
    {
        return $this->getData(self::USERNAME);
    }

    /**
     * @inheritdoc
     */
    public function setUsername(string $username)
    {
        $this->setData(self::USERNAME, $username);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExecutionTime(): ?string
    {
        return $this->getData(self::EXECUTION_TIME);
    }

    /**
     * @inheritdoc
     */
    public function setExecutionTime(string $executionTime)
    {
        $this->setData(self::EXECUTION_TIME, $executionTime);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRequest(): ?string
    {
        return $this->getData(self::REQUEST);
    }

    /**
     * @inheritdoc
     */
    public function setRequest(string $request)
    {
        $this->setData(self::REQUEST, $request);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFiles(): array
    {
        return $this->getData(self::FILES) ?? [];
    }

    /**
     * @inheritdoc
     */
    public function setFiles(array $files = []): AuditRequestInterface
    {
        $this->setData(self::FILES, $files);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addFile(FileInterface $file): AuditRequestInterface
    {
        $files = $this->getFiles();
        $files[] = $file;
        return $this->setFiles($files);
    }
}
