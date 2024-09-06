<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\Data\AuditInterface;
use Magento\Framework\Model\AbstractModel;

class Audit extends AbstractModel implements AuditInterface
{

    protected $_idFieldName = self::ID;

    protected function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\ResourceModel\Audit::class);
    }

    public function getUser(): ?string
    {
        return $this->getData(self::USER);
    }

    public function setUser(string $user)
    {
        $this->setData(self::USER, $user);
        return $this;
    }

    public function getDate(): ?string
    {
        return $this->getData(self::DATE);
    }

    public function setDate(string $date)
    {
        $this->setData(self::DATE, $date);
        return $this;
    }

    public function getFilepath(): ?string
    {
        return $this->getData(self::FILEPATH);
    }

    public function setFilepath(string $filepath)
    {
        $this->setData(self::FILEPATH, $filepath);
        return $this;
    }

    public function getOverallResult(): ?string
    {
        return $this->getData(self::OVERALL_RESULT);
    }

    public function setOverallResult(string $overallResult)
    {
        $this->setData(self::OVERALL_RESULT, $overallResult);
        return $this;
    }
}
