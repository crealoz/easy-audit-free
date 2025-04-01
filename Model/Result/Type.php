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

use Crealoz\EasyAudit\Api\Data\TypeInterface;
use Magento\Framework\Model\AbstractModel;

class Type extends AbstractModel implements TypeInterface
{
    protected $_idFieldName = self::ID;

    /**
     * @inheritDoc
     */
    public function getTypeId(): int
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setTypeId(int $type_id): self
    {
        $this->setData(self::ID, $type_id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): self
    {
        $this->setData(self::NAME, $name);
        return $this;
    }
}
