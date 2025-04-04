<?php


namespace Crealoz\EasyAudit\Processor\Database;

use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractAuditProcessor;
use Magento\Framework\App\ResourceConnection;

/**
 * This processor checks if the following tables are heavy and can slow down your database. Consider optimizing them.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 * @package Crealoz\EasyAudit\Processor\Database
 */
class HeavyTables extends AbstractAuditProcessor implements AuditProcessorInterface
{
    public const ORDER = 10;

    public const TAG = 'heavyTables';

    private array $tablesToCheck = [
        'dataflow_batch_export',
        'dataflow_batch_import',
        'log_customer',
        'log_quote',
        'log_summary',
        'log_summary_type',
        'log_url',
        'log_url_info',
        'log_visitor',
        'log_visitor_info',
        'log_visitor_online',
        'report_viewed_product_index',
        'report_compared_product_index',
        'report_event',
        'index_event',
        'catalog_compare_item',
        'catalogindex_aggregation',
        'catalogindex_aggregation_tag',
        'catalogindex_aggregation_to_tag',
        'adminnotification_inbox',
        'aw_core_logger'
    ];

    public function __construct(
        AuditStorage $auditStorage,
        protected readonly ResourceConnection $resourceConnection,
    )
    {
        parent::__construct($auditStorage);
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasError' => false,
            'errors' => [],
            'warnings' => [
                'heavyTables' => $this->getHeavyTablesWarningEntry(),
            ],
        ];
    }

    private function getHeavyTablesWarningEntry(): array
    {
        $title = __('Heavy DB Tables');
        $explanation = __('The following tables are known to be heavy and can slow down your database. Consider optimizing them.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'tables' => [],
        ];
    }

    public function run(): void
    {
        $connection = $this->resourceConnection->getConnection();
        foreach ($this->tablesToCheck as $table) {
            $tableName = $connection->getTableName($table);
            if ($connection->isTableExists($tableName)) {
                $size = $connection->fetchOne("SELECT COUNT(*) FROM {$tableName}");
                if ($size > 10000) {
                    $this->results['hasError'] = true;
                    $this->results['warnings']['heavyTables']['tables'][$tableName] = $size;
                }
            }
        }
    }

    public function getProcessorName(): string
    {
        return __('Heavy DB Tables');
    }

    public function getAuditSection(): string
    {
        return __('Database');
    }
}
