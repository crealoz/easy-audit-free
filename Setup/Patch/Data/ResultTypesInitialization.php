<?php


namespace Crealoz\EasyAudit\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class ResultTypesInitialization implements DataPatchInterface, PatchRevertableInterface
{
    private array $types = [
        'files',
        'database',
        'module',
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('crealoz_easyaudit_result_type'),
            ['name'],
            $this->types
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('crealoz_easyaudit_result_type'),
            ['type_id IN (?)' => array_keys($this->types)]
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
