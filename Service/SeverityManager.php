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

use Crealoz\EasyAudit\Api\SeverityRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class SeverityManager
{

    protected array $severities;

    public function __construct(
        protected readonly SeverityRepositoryInterface $severityRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
    )
    {
    }

    private function initSeverity()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $severities = $this->severityRepository->getList($searchCriteria)->getItems();
        $this->severities = [];
        foreach ($severities as $severity) {
            $this->severities[$severity->getId()] = [
                'level' => $severity->getLevel(),
                'color' => $severity->getColor()
            ];
        }
    }

    public function getSeverity($severityId)
    {
        if (!isset($this->severities)) {
            $this->initSeverity();
        }
        return $this->severities[$severityId];
    }

}
