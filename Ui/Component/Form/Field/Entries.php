<?php


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

    public function prepareDataSource(array $dataSource): array
    {
        try {
            if (isset($dataSource['data']['general'])) {
                foreach ($dataSource['data']['general'] as $name => $value) {
                    if ($name === 'result_id' && $this->entryRepository->hasEntries($value)) {
                        $dataSource['data']['general']['entries'] = $this->manageEntries($value);
                    }
                }
            }
            return $dataSource;
        } catch (\Exception $e) {
            // Log the error or handle it gracefully
            return $dataSource;
        }
    }

    private function manageEntries($resultId): string
    {
        $entriesString = [];
        $entriesData = $this->entryRepository->getEntriesByResultId($resultId);
        foreach ($entriesData as $entry) {
            if ($entry->getEntry() === '') {
                continue;
            }

            $entryLines = [nl2br($entry->getEntry())];

            if ($this->subEntryRepository->hasSubEntries($entry->getEntryId())) {
                $subEntries = $this->subEntryRepository->getSubEntriesByEntryId($entry->getEntryId());
                $entryLines = array_merge(
                    $entryLines,
                    array_map(fn($subEntry) => ' - ' . $subEntry->getSubentry(), $subEntries)
                );
            }

            $entriesString[] = implode('<br>', $entryLines);
        }

        return implode('<br>', $entriesString);
    }
}
