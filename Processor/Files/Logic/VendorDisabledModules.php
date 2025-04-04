<?php


namespace Crealoz\EasyAudit\Processor\Files\Logic;

use Crealoz\EasyAudit\Api\Processor\Audit\ArrayProcessorInterface;
use Crealoz\EasyAudit\Exception\Processor\Modules\EnabledStatusIrretrievableException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractArrayProcessor;
use Crealoz\EasyAudit\Service\ModuleTools;
use Crealoz\EasyAudit\Processor\Files\Logic\Modules\CheckEnabled;
use Magento\Framework\Exception\FileSystemException;

/**
 * This processor checks if the modules are disabled in the codebase but still active. It will try to get the status of the modules using some known generic method and may not be accurate. Please verify manually.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class VendorDisabledModules extends AbstractArrayProcessor implements ArrayProcessorInterface
{
    public const ORDER = 20;

    public const TAG = 'disabledModules';

    public function __construct(
        AuditStorage                  $auditStorage,
        private readonly ModuleTools  $moduleTools,
        private readonly CheckEnabled $checkEnabled
    )
    {
        parent::__construct($auditStorage);
    }

    public function getAuditSection(): string
    {
        return __('Logic');
    }

    public function getProcessorName(): string
    {
        return __('Disabled Modules');
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [],
            'warnings' => [
                'enabledModules' => $this->getEnabledModulesEntry()
            ],
            'suggestions' => [
                'irretrievableConfig' => $this->getIrretrievableConfigEntry()
            ]
        ];
    }

    private function getIrretrievableConfigEntry(): array
    {
        $title = __('Could not check \'enabled\' status of modules');
        $explanation = __('The status of the following modules could not be retrieved. A manual check can be necessary.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'irretrievableConfig'
        ];
    }

    private function getEnabledModulesEntry(): array
    {
        $title = __('Active modules not enabled');
        $explanation = __('The following modules are active but not enabled. Consider deactivating them or removing them from the codebase.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'caution' => 'This check tries to get the status using some known generic method and may not be accurate. Please verify manually.',
            'specificSections' => 'manageUnusedModules'
        ];
    }

    /**
     * @throws FileSystemException
     */
    public function run(): void
    {
        $enabledModules = $this->moduleTools->getEnabledModules();
        if ($enabledModules !== []) {
            foreach ($this->getArray() as $moduleDeclarationPath) {
                $moduleName = $this->moduleTools->getModuleNameByModuleXml($moduleDeclarationPath);
                if (str_contains($moduleName, 'Test') || str_contains($moduleName, 'Magento_A') || str_contains($moduleName, 'Magento_B')) {
                    continue;
                }
                try {
                    if (in_array($moduleName, $enabledModules) && !$this->checkEnabled->isModuleEnabled($moduleDeclarationPath)) {
                        $this->results['hasErrors'] = true;
                        $this->results['warnings']['enabledModules']['files'][] = $moduleName;
                    }
                } catch (EnabledStatusIrretrievableException $e) {
                    $this->results['hasErrors'] = true;
                    $this->results['suggestions']['irretrievableConfig']['files'][] = $moduleName;
                }
            }
        }
    }
}
