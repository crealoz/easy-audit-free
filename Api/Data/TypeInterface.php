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

namespace Crealoz\EasyAudit\Api\Data;

interface TypeInterface
{
    const ID = 'type_id';
    const NAME = 'name';

    /**
     * @return int
     */
    public function getTypeId(): int;

    /**
     * @param int $type_id
     * @return self
     */
    public function setTypeId(int $type_id): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self;
}
