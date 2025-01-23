<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Crealoz\EasyAudit\Api\FileSystem\FilterInterface;

class FilterGetter
{
    /**
     * @readonly
     */
    private array $filterClasses = [];
    private array $filters = [];

    private bool $isFiltersInitialized = false;

    public function __construct(array $filterClasses = [])
    {
        $this->filterClasses = $filterClasses;
    }

    public function getFilters(): array
    {
        if (!$this->isFiltersInitialized) {
            foreach ($this->filterClasses as $type => $filterClass) {
                if (!$filterClass instanceof FilterInterface) {
                    throw new \InvalidArgumentException('Filter class must implement FilterInterface');
                }
                $this->filters[$type] = $filterClass->retrieve();
            }
            $this->isFiltersInitialized = true;
        }

        return $this->filters;
    }

    public function getFilter(string $type): array
    {
        $filters = $this->getFilters();
        if (!isset($filters[$type])) {
            throw new \InvalidArgumentException('Unknown filter type: ' . $type);
        }
        return $filters[$type];
    }
}