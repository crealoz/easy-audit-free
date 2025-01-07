<?php

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractFileProcessor;
use Crealoz\EasyAudit\Service\Audit;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

class HardWrittenSQL extends AbstractFileProcessor implements FileProcessorInterface
{

    public function getProcessorName(): string
    {
        return __('Hard Written SQL');
    }

    public function getAuditSection(): string
    {
        return __('PHP');
    }

    public function __construct(
        AuditStorage $auditStorage,
        protected readonly DriverInterface $driver
    )
    {
        parent::__construct($auditStorage);
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'hardWrittenSQLSelect' => $this->getHardWrittenSQLSelectEntry(),
                'hardWrittenSQLDelete' => $this->getHardWrittenSQLDeleteEntry()
            ],
            'warnings' => [
                'hardWrittenSQLUpdate' => $this->getHardWrittenSQLUpdateEntry(),
                'hardWrittenSQLInsert' => $this->getHardWrittenSQLInsertEntry()
            ],
            'suggestions' => [
                'hardWrittenSQLJoin' => $this->getHardWrittenSQLJoinEntry()
            ]
        ];
    }

    private function getHardWrittenSQLSelectEntry(): array
    {
        $title = __('Hard Written SQL SELECT');
        $explanation = __('SELECT queries must be avoided. Use the Magento Framework methods instead or a custom repository with getList and/or getById methods.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    private function getHardWrittenSQLDeleteEntry(): array
    {
        $title = __('Hard Written SQL DELETE');
        $explanation = __('DELETE queries must be avoided. Use the Magento Framework methods instead or a custom repository with delete and/or deleteById methods.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    private function getHardWrittenSQLUpdateEntry(): array
    {
        $title = __('Hard Written SQL UPDATE');
        $explanation = __('UPDATE queries should be avoided. Use the Magento Framework methods instead or a custom repository with a save method. It can be faster for large amounts of data but it can lead to data loss in case of an update.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    private function getHardWrittenSQLInsertEntry(): array
    {
        $title = __('Hard Written SQL INSERT');
        $explanation = __('INSERT queries should be avoided. Use the Magento Framework methods instead or a custom repository with a save method. It can be faster for large amounts of data but it can lead to data loss in case of an update.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    private function getHardWrittenSQLJoinEntry(): array
    {
        $title = __('Hard Written SQL JOIN');
        $explanation = __('JOIN queries should be avoided. Use the Magento Framework methods instead.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    /**
     * @throws FileSystemException
     * @todo ignore modules
     */
    public function run(): void
    {
        $input = $this->getFile();
        $code = $this->driver->fileGetContents($input);
        if (str_contains($code, 'SELECT')) {
            /**
             * Tries to find a FROM clause in the SQL query
             */
            preg_match('/SELECT.*FROM/', $code, $matches);
            if (!empty($matches)) {
                $this->results['hasErrors'] = true;
                $this->results['errors']['hardWrittenSQLSelect']['files'][] = $input;
                $this->addErroneousFile($input, Audit::PRIORITY_AVERAGE);
            }
        }
        if (str_contains($code, 'INSERT')) {
            /**
             * Tries to find an INTO clause in the SQL query
             */
            preg_match('/INSERT.*INTO/', $code, $matches);
            if (!empty($matches)) {
                $this->results['hasErrors'] = true;
                $this->results['warnings']['hardWrittenSQLInsert']['files'][] = $input;
                $this->addErroneousFile($input, Audit::PRIORITY_AVERAGE);
            }
        }
        if (str_contains($code, 'UPDATE')) {
            /**
             * Tries to find a SET clause in the SQL query
             */
            preg_match('/UPDATE.*SET/', $code, $matches);
            if (!empty($matches)) {
                $this->results['hasErrors'] = true;
                $this->results['warnings']['hardWrittenSQLUpdate']['files'][] = $input;
                $this->addErroneousFile($input, Audit::PRIORITY_AVERAGE);
            }
        }
        if (str_contains($code, 'DELETE')) {
            /**
             * Tries to find a FROM clause in the SQL query
             */
            preg_match('/DELETE.*FROM/', $code, $matches);
            if (!empty($matches)) {
                $this->results['hasErrors'] = true;
                $this->results['errors']['hardWrittenSQLDelete']['files'][] = $input;
                $this->addErroneousFile($input, Audit::PRIORITY_AVERAGE);
            }
        }
        if (str_contains($code, 'JOIN')) {
            /**
             * Tries to find a JOIN sth ON clause in the SQL query
             */
            preg_match('/JOIN.*ON/', $code, $matches);
            if (!empty($matches)) {
                $this->results['hasErrors'] = true;
                $this->results['suggestions']['hardWrittenSQLJoin']['files'][] = $input;
            }
        }
    }

    public function getProcessorTag(): string
    {
        return 'hardWrittenSQL';
    }
}