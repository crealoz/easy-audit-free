<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
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

    public function getUsername(): ?string
    {
        return $this->getData(self::USERNAME);
    }

    public function setUsername(string $username)
    {
        $this->setData(self::USERNAME, $username);
        return $this;
    }

    public function getExecutionTime(): ?string
    {
        return $this->getData(self::EXECUTION_TIME);
    }

    public function setExecutionTime(string $executionTime)
    {
        $this->setData(self::EXECUTION_TIME, $executionTime);
        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    public function getRequest(): ?string
    {
        return $this->getData(self::REQUEST);
    }

    public function setRequest(string $request)
    {
        $this->setData(self::REQUEST, $request);
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->getData(self::FILE_PATH);
    }

    public function setFilePath(string $filePath)
    {
        $this->setData(self::FILE_PATH, $filePath);
        return $this;
    }
}
