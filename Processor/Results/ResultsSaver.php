<?php


namespace Crealoz\EasyAudit\Processor\Results;

use Crealoz\EasyAudit\Service\PDFWriter\SpecificSectionGetter;
use Crealoz\EasyAudit\Api\Data\SeverityInterface;
use Crealoz\EasyAudit\Api\Data\TypeInterface;
use Crealoz\EasyAudit\Api\SeverityRepositoryInterface;
use Crealoz\EasyAudit\Api\TypeRepositoryInterface;
use Crealoz\EasyAudit\Model\ResourceModel\Result;
use Crealoz\EasyAudit\Model\ResultFactory;
use Crealoz\EasyAudit\Model\Result\EntryFactory;
use Crealoz\EasyAudit\Service\PrManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Saves the results in database for later use
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class ResultsSaver implements \Crealoz\EasyAudit\Api\Processor\ResultProcessorInterface
{
    private readonly SeverityInterface $errorSeverity;
    private readonly SeverityInterface $warningSeverity;
    private readonly SeverityInterface $suggestionSeverity;

    private readonly TypeInterface $fileType;

    private readonly TypeInterface $databaseType;

    private readonly TypeInterface $moduleType;

    private $requestId;

    public function __construct(
        private readonly ResultFactory               $resultFactory,
        private readonly EntryFactory                $entryFactory,
        private readonly SeverityRepositoryInterface $severityRepository,
        private readonly TypeRepositoryInterface     $typeRepository,
        private readonly ResourceConnection          $connection,
        private readonly Result                      $resultResource,
        private readonly SpecificSectionGetter       $specificSectionGetter,
        private readonly PrManager                   $prManager,
        private readonly LoggerInterface             $logger

    )
    {
        $this->errorSeverity = $this->severityRepository->getByLevel('error');
        $this->warningSeverity = $this->severityRepository->getByLevel('warning');
        $this->suggestionSeverity = $this->severityRepository->getByLevel('suggestion');
        $this->fileType = $this->typeRepository->getByType('files');
        $this->databaseType = $this->typeRepository->getByType('database');
        $this->moduleType = $this->typeRepository->getByType('module');
    }

    public function processResults(array $results): array
    {
        $this->requestId = $results['data']['requestId'];
        foreach ($results as $processor => $result) {
            // skip introduction and erroneousFiles
            if ($processor === 'introduction' || $processor === 'erroneousFiles' || $processor === 'data') {
                continue;
            }
            foreach ($result as $entry) {
                $this->manageEntries($entry);
            }
        }
        return $results;
    }

    /**
     * @param array $entries
     * @return void
     */
    private function manageEntries(array $entries): void
    {
        if (isset($entries['hasErrors']) && $entries['hasErrors']) {
            $errors = $entries['errors'] ?? [];
            $warnings = $entries['warnings'] ?? [];
            $suggestions = $entries['suggestions'] ?? [];
            if (!empty($errors)) {
                $this->storeResults($errors, $this->errorSeverity);
            }
            if (!empty($warnings)) {
                $this->storeResults($warnings, $this->warningSeverity);
            }
            if (!empty($suggestions)) {
                $this->storeResults($suggestions, $this->suggestionSeverity);
            }
        }
    }

    /**
     * @param array $results
     * @param SeverityInterface $severity
     * @return void
     */
    private function storeResults(array $results, SeverityInterface $severity): void
    {
        $resultsToStore = [];
        foreach ($results as $processor => $result) {
            if (empty($result['files'])) {
                // No result, no problem
                continue;
            }
            if (!empty($result['specificSections'])) {
                $specificSection = $this->specificSectionGetter->getSpecificSection($result['specificSections']);
                $entries = [
                    [
                        'entry' => $specificSection->getPHPFormatedText($processor, $result['files']),
                        'subEntries' => [],
                        'type' => $this->fileType->getTypeId()
                    ]
                ];
            } else {
                $entries = $this->getEntries($result['files']);
            }

            if (empty($result['title']) || empty($result['explanation'])) {
                continue;
            }

            $dbResult = [
                'severity' => $severity,
                'processor' => $processor,
                'title' => $result['title'],
                'summary' => $result['explanation'],
                'entries' => $entries,
                'pr_enabled' => $this->prManager->isPrEnabled($processor)
            ];
            $resultsToStore[] = $dbResult;
        }

        $this->saveResultsByBunches($resultsToStore);

    }

    /**
     * @param array $entries
     * @return array
     */
    private function getEntries(array $entries): array
    {
        if ($entries === []) {
            return [];
        }
        $dbEntries = [];
        foreach ($entries as $entry => $subEntry) {
            $dbSubEntries = [];
            if (is_array($subEntry)) {
                $dbSubEntries = $this->getEntries($subEntry);
            } else {
                $entry = $subEntry;
            }
            $dbEntries[] = [
                'entry' => $entry,
                'subEntries' => $dbSubEntries,
                'type' => $this->fileType->getTypeId()
            ];
        }
        return $dbEntries;
    }

    /**
     * @param array $results
     * @return void
     */
    private function saveResultsByBunches(array $results): void
    {
        $bunchSize = 100;
        $bunches = array_chunk($results, $bunchSize);
        $writeConnection = $this->connection->getConnection();
        foreach ($bunches as $bunch) {
            $writeConnection->beginTransaction();
            try {
                foreach ($bunch as $result) {
                    $this->writeResultInDataBase($result, $writeConnection);
                }
                $writeConnection->commit();
            } catch (\Exception $e) {
                $writeConnection->rollBack();
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @param array $result
     * @param AdapterInterface $writeConnection
     * @return void
     * @throws LocalizedException
     */
    private function writeResultInDataBase(array $result, AdapterInterface $writeConnection): void
    {
        $writeConnection->insert(
            $this->resultResource->getMainTable(),
            [
                'request_id' => $this->requestId,
                'severity_id' => $result['severity']->getSeverityId(),
                'processor' => $result['processor'],
                'title' => $result['title'],
                'summary' => $result['summary']
            ]
        );
        $resultId = $writeConnection->lastInsertId();
        foreach ($result['entries'] as $entry) {
            $writeConnection->insert(
                $this->resultResource->getTable('crealoz_easyaudit_result_entry'),
                [
                    'result_id' => $resultId,
                    'entry' => $entry['entry'],
                    'type_id' => $entry['type']
                ]
            );
            if (empty($entry['subEntries'])) {
                continue;
            }
            $entryId = $writeConnection->lastInsertId();
            foreach ($entry['subEntries'] as $subEntry) {
                $text = $subEntry['entry'];
                if (is_array($subEntry['subEntries'])) {
                    $text .= PHP_EOL . implode(PHP_EOL, $subEntry['subEntries']);
                }
                $writeConnection->insert(
                    $this->resultResource->getTable('crealoz_easyaudit_result_subentry'),
                    [
                        'entry_id' => $entryId,
                        'subentry' => $text,
                    ]
                );
            }
        }
    }
}
