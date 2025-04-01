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

interface SeverityInterface
{
    const ID = 'severity_id';
    const LEVEL = 'level';
    const COLOR = 'color';

    /**
     * @return int
     */
    public function getSeverityId(): int;

    /**
     * @param int $severity_id
     * @return self
     */
    public function setSeverityId(int $severity_id): self;

    /**
     * @return string
     */
    public function getLevel(): string;

    /**
     * @param string $level
     * @return self
     */
    public function setLevel(string $level): self;

    /**
     * @return string
     */
    public function getColor(): string;

    /**
     * @param string $color
     * @return self
     */
    public function setColor(string $color): self;
}
