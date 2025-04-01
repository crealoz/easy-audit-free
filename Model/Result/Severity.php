<?php
/**
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */

namespace Crealoz\EasyAudit\Model\Result;

use Crealoz\EasyAudit\Api\Data\SeverityInterface;
use Magento\Framework\Model\AbstractModel;

class Severity extends AbstractModel implements SeverityInterface
{
    protected $_idFieldName = self::ID;

    /**
     * @return int
     */
    public function getSeverityId(): int
    {
        return $this->getData(self::ID);
    }

    /**
     * @param int $severity_id
     * @return self
     */
    public function setSeverityId(int $severity_id): self
    {
        $this->setData(self::ID, $severity_id);
        return $this;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->getData(self::LEVEL);
    }

    /**
     * @param string $level
     * @return self
     */
    public function setLevel(string $level): self
    {
        $this->setData(self::LEVEL, $level);
        return $this;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->getData(self::COLOR);
    }

    /**
     * @param string $color
     * @return self
     */
    public function setColor(string $color): self
    {
        $this->setData(self::COLOR, $color);
        return $this;
    }
}
