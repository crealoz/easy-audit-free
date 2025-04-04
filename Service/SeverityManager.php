<?php


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
