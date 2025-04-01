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

namespace Crealoz\EasyAudit\Service\PrManager;

use Crealoz\EasyAudit\Exception\InvalidPrType;
use Magento\Framework\ObjectManagerInterface;

class BodyPreparerFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {
    }

    /**
     * @param $type
     * @return BodyPreparerInterface
     * @throws InvalidPrType
     */
    public function create($type): BodyPreparerInterface
    {
        return match ($type) {
            'aroundToBeforePlugin', 'aroundToAfterPlugin' => $this->objectManager->create(AroundFunctions::class),
            'noProxyUsedInCommands' => $this->objectManager->create(NoProxyUsedInCommands::class),
            default => throw new InvalidPrType(__('Invalid type %1', $type)),
        };
    }
}
