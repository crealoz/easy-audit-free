<?php


namespace Crealoz\EasyAudit\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class ResultSeverityInitialization implements DataPatchInterface, PatchRevertableInterface
{
    private array $severities = [
        ['level' => 'error', 'color' => 'FF0000'],
        ['level' => 'warning', 'color' => 'FFA500'],
        ['level' => 'suggestion', 'color' => 'FFFF00'],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    )
    {
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('crealoz_easyaudit_result_severity'),
            ['level', 'color'],
            $this->severities
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('crealoz_easyaudit_result_severity'),
            ['level IN (?)' => array_column($this->severities, 'level')]
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
