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

namespace Crealoz\EasyAudit\Service\FileSystem;

class PhtmlUtils
{
    public function getThisInPhtmlFile($content): bool
    {
        return str_contains((string) $content, '$this');
    }

    public function snakeToCamelCase($string): string
    {
        return str_replace('_', '', ucwords((string) $string, '_'));
    }
}
