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

namespace Crealoz\EasyAudit\Service;

class PrepareMarkdownBody
{
    public function execute($result)
    {
        $body = $result->getSummary();
        $body .= "\n\n### Entries\n";
        foreach ($result->getEntries() as $entry) {
            $body .= "\n\n" . $entry->getEntry();
            foreach ($entry->getSubEntries() as $subEntry) {
                $body .= "\n- " . $subEntry->getSubentry();
            }
        }
        return $body;
    }
}
