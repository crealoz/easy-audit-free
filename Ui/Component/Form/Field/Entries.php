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

namespace Crealoz\EasyAudit\Ui\Component\Form\Field;

use Crealoz\EasyAudit\Api\EntryRepositoryInterface;
use Crealoz\EasyAudit\Api\SubEntryRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Entries extends \Magento\Ui\Component\Form\Field
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        protected readonly EntryRepositoryInterface $entryRepository,
        protected readonly SubEntryRepositoryInterface $subEntryRepository,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['general'])) {
            foreach ($dataSource['data']['general'] as $name => $value) {
                if ($name === 'result_id' && $this->entryRepository->hasEntries($value)) {
                    $dataSource['data']['general']['entries'] = $this->manageEntries($value);
                }
            }
        }

        return $dataSource;
    }

    private function manageEntries($resultId)
    {
        $entriesString = '';
        $entriesData = $this->entryRepository->getEntriesByResultId($resultId);
        foreach ($entriesData as $entry) {
            if ($entry->getEntry() === null) {
                continue;
            }
            $entriesString .= nl2br($entry->getEntry()) . '<br>';
            if (!$this->subEntryRepository->hasSubEntries($entry->getEntryId())) {
                continue;
            }
            foreach ($this->subEntryRepository->getSubEntriesByEntryId($entry->getEntryId()) as $subEntry) {
                $entriesString .= ' - ' . $subEntry->getSubentry() . '<br>';
            }
        }
        return $entriesString;
    }
}
