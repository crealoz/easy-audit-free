<?php

namespace Crealoz\EasyAudit\Service\Processor\Code;

use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\Processor\AbstractProcessor;
use Crealoz\EasyAudit\Service\Processor\ProcessorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

class HardWrittenSQL extends AbstractProcessor implements ProcessorInterface
{

    protected string $processorName = 'Hard Written SQL';

    protected string $auditSection = 'PHP';

    public function __construct(
        protected readonly DriverInterface $driver
    )
    {
    }

    protected array $results = [
        'hasErrors' => false,
        'errors' => [
            'hardWrittenSQLSelect' => [
                'title' => 'Hard Written SQL SELECT',
                'explanation' => 'SELECT queries must be avoided. Use the Magento Framework methods instead or a custom 
                    repository with a getList and/or getById methods.',
                'files' => []
            ],
            'hardWrittenSQLDelete' => [
                'title' => 'Hard Written SQL DELETE',
                'explanation' => 'DELETE queries must be avoided. Use the Magento Framework methods instead or a custom 
                    repository with a delete and/or deleteById methods.',
                'files' => []
            ]
        ],
        'warnings' => [
            'hardWrittenSQLUpdate' => [
                'title' => 'Hard Written SQL UPDATE',
                'explanation' => 'UPDATE queries should be avoided. Use the Magento Framework methods instead or a
                    custom repository with a save method. It can be faster for large amounts of data but it can lead to
                    data loss in case of an update.',
                'files' => []
            ],
            'hardWrittenSQLInsert' => [
                'title' => 'Hard Written SQL INSERT',
                'explanation' => 'INSERT queries should be avoided. Use the Magento Framework methods instead or a 
                    custom repository with a save method. It can be faster for large amounts of data but it can lead to
                    data loss in case of an update.',
                'files' => []
            ],
        ],
        'suggestions' => [
            'hardWrittenSQLJoin' => [
                'title' => 'Hard Written SQL JOIN',
                'explanation' => 'JOIN queries should be avoided. Use the Magento Framework methods instead.',
                'files' => []
            ],
        ]
    ];

    /**
     * @throws FileSystemException
     */
    public function run($input)
    {
        $code = $this->driver->fileGetContents($input);
        if (str_contains($code, 'SELECT') ) {
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
        if (str_contains($code, 'INSERT') ) {
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
        if (str_contains($code, 'UPDATE') ) {
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
        if (str_contains($code, 'DELETE') ) {
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
        if (str_contains($code, 'JOIN') ) {
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
}