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

namespace Crealoz\EasyAudit\Processor\Database;

use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractAuditProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This processor checks if flat catalog is enabled for any store. Flat catalog should be disabled for the recent versions of Magento 2.1 and above.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 * @package Crealoz\EasyAudit\Processor\Database
 */
class FlatCatalog extends AbstractAuditProcessor implements AuditProcessorInterface
{
    public const ORDER = 20;

    public const TAG = 'flatCatalog';

    public function __construct(
        AuditStorage $auditStorage,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($auditStorage);
    }

    public function run(): void
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getId();
            $flatCatalog = $this->scopeConfig->getValue('catalog/frontend/flat_catalog_product', 'store', $storeId);
            if ($flatCatalog) {
                $this->results['hasErrors'] = true;
                $this->results['errors']['flatCatalog']['product'][] = $store->getName();
            }
            $flatCatalog = $this->scopeConfig->getValue('catalog/frontend/flat_catalog_category', 'store', $storeId);
            if ($flatCatalog) {
                $this->results['hasErrors'] = true;
                $this->results['errors']['flatCatalog']['category'][] = $store->getName();
            }
        }
    }

    public function getProcessorName(): string
    {
        return __('Check flat catalog for stores');
    }

    public function getAuditSection(): string
    {
        return __('Database');
    }

    public function getErroneousFiles(): array
    {
        return [];
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'flatCatalog' => $this->getFlatCatalogEntry()
            ],
            'warnings' => [],
            'suggestions' => []
        ];
    }

    private function getFlatCatalogEntry(): array
    {
        $title = __('Flat catalog is enabled for some stores');
        $explanation = __('Flat catalog should be disabled for the recent versions of Magento 2.1 and above. It is recommended to disable flat catalog for all stores.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'manageFlatCatalog'
        ];
    }
}
