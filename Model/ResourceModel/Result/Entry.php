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

namespace Crealoz\EasyAudit\Model\ResourceModel\Result;

use Crealoz\EasyAudit\Api\Data\EntryInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Entry extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('crealoz_easyaudit_result_entry', EntryInterface::ID);
    }
}
