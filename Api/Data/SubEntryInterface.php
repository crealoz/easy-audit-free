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

interface SubEntryInterface 
{
    const ID = 'subentry_id';
    const ENTRY_ID = 'entry_id';
    const SUBENTRY = 'subentry';

    public function getSubentryId();
    public function setSubentryId(int $subentry_id);
    public function getEntryId();
    public function setEntryId(int $entry_id);
    public function getSubentry();
    public function setSubentry(string $subentry);
}
