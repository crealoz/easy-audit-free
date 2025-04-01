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

namespace Crealoz\EasyAudit\Service\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;

class MiddlewareHost
{

    private string $host = 'https://api.crealoz.fr';

    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
        private readonly ScopeConfigInterface $scopeConfig
    )
    {
    }

    public function getHost(): string
    {
        return $this->deploymentConfig->get('easy_audit/middleware/host') ?? $this->host;
    }

    public function isSelfSigned(): bool
    {
        return (bool) $this->deploymentConfig->get('easy_audit/middleware/self_signed');
    }

    public function getKey(): string
    {
        return $this->deploymentConfig->get('easy_audit/middleware/key') ?? $this->scopeConfig->getValue('easy_audit/general/key');
    }

    public function getHash(): string
    {
        return $this->deploymentConfig->get('easy_audit/middleware/hash') ?? $this->scopeConfig->getValue('easy_audit/general/hash');
    }
}
