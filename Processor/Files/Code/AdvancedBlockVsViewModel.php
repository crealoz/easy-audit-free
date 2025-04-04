<?php


namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractXmlProcessor;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\ModuleTools;
use Crealoz\EasyAudit\Exception\Processor\Modules\IgnoredModuleException;
use Crealoz\EasyAudit\Service\FileSystem\PhtmlUtils;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * This processor checks if the phtml files are using blocks to retrieve data or configuration. It also checks if the file is using $this instead of $block.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 * @package Crealoz\EasyAudit\Processor\Files\Code
 */
class AdvancedBlockVsViewModel extends AbstractXmlProcessor implements FileProcessorInterface
{
    public const ORDER = 41;

    public const TAG = 'advancedBlockVsVM';

    private array $namespaces = [];

    public function __construct(
        AuditStorage $auditStorage,
        private readonly ModuleTools $moduleTools,
        private readonly ModulePaths $modulePaths,
        protected readonly DriverInterface $driver,
        private readonly PhtmlUtils $phtmlUtils
    ) {
        parent::__construct($auditStorage);
    }

    public function getProcessorName(): string
    {
        return 'Advanced block vs ViewModel';
    }

    public function getAuditSection(): string
    {
        return 'PHP';
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();

        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'useOfThisInsteadOfBlock' => $this->getUseOfThisInsteadOfBlockEntry(),
                ],
            'warnings' => [
                'dataCrunchInPhtml' => $this->getDataCrunchInPhtmlEntry(),
                'directBlockDataCrunch' => $this->getDirectBlockDataCrunchEntry(),
            ],
        ];
    }

    private function getDataCrunchInPhtmlEntry(): array
    {
        $title = __('Data crunch in phtml files');
        $explanation = __('Some data may be retrieved using blocks instead of ViewModels in these files. Reviewing this approach and considering ViewModels could be beneficial, as they provide a more structured way to handle data logic, improving separation of concerns between presentation and business logic.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'advancedBlockVsVM'
        ];
    }

    private function getDirectBlockDataCrunchEntry(): array
    {
        $title = __('Direct block data crunch');
        $explanation = __('Using blocks to retrieve data or configuration is generally discouraged, as ViewModels allow for a clearer separation of logic and presentation. Opting for ViewModels is recommended for a more structured approach.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'advancedBlockVsVM'
        ];
    }

    private function getUseOfThisInsteadOfBlockEntry(): array
    {
        $title = __('Use of $this instead of $block');
        $explanation = __('Using $this in a block class is not recommended, as it may not be compatible with templating systems beyond Magento’s default. Instead, using $block ensures broader compatibility and supports a more adaptable templating structure.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    /**
     * @throws FileSystemException|\ReflectionException
     */
    public function run(): void
    {
        try {
            $customBlocks = $this->getAllCustomBlocks($this->getFile());
        } catch (IgnoredModuleException $e) {
            return;
        }
        foreach ($customBlocks['sameModule'] as $block => $files) {
            $methods = $this->getMethodsFromBlock($block);
            $this->checkPhtmlFiles($files, $methods);
        }
        foreach ($customBlocks['differentModule'] as $files) {
            $this->checkPhtmlFiles($files);
        }
    }

    /**
     * Parses layout and retrieves all custom blocks either from the same module or from a different module but with the
     * phtml file in the module.
     * It throws IgnoredModuleException if the module is ignored.
     *
     * @throws FileSystemException|IgnoredModuleException
     */
    private function getAllCustomBlocks($input): array
    {
        $moduleName = $this->moduleTools->getModuleNameByAnyFile($input);
        if ($this->auditStorage->isModuleIgnored($moduleName)) {
            throw new IgnoredModuleException($moduleName);
        }
        $moduleFrontendPath = $this->modulePaths->getFrontendPath($input);
        $vendorModuleName = implode('/', explode('_', $moduleName));

        $content = $this->getContent();
        $this->namespaces = $content->getNamespaces(true);
        $blocks = $content->xpath('//block');
        $customBlocks = [
            'sameModule' => [],
            'differentModule' => []
        ];
        foreach ($blocks as $block) {
            /** @var \SimpleXMLElement $block */
            if (!$block['class'] || !$block['template'] || !str_contains((string)$block['template'], $moduleName)) {
                continue;
            }
            $blockType = (string)$block['class'];
            // Remove Vendor_Module:: prefix
            $template = str_replace($moduleName . '::', '', (string)$block['template']);
            if (str_contains($blockType, $vendorModuleName)) {
                if (!isset($customBlocks['sameModule'][$blockType])) {
                    $customBlocks['sameModule'][$blockType] = [
                        'viewModels' => $this->getDeclaredViewModels($block)
                    ];
                }
                $customBlocks['sameModule'][$blockType]['files'][] = $input;
            } else {
                if (!isset($customBlocks['differentModule'][$blockType])) {
                    $customBlocks['differentModule'][$blockType] = [
                        'viewModels' => $this->getDeclaredViewModels($block)
                    ];
                }
                $customBlocks['differentModule'][$blockType]['files'][] = $input;
            }
        }
        return $customBlocks;
    }

    private function getDeclaredViewModels(\SimpleXMLElement $content): array
    {
        $viewModelClasses = [];
        $argumentNodes = $content->xpath('//argument');
        foreach ($argumentNodes as $argumentNode) {
            /** @var \SimpleXMLElement $argumentNode */
            if (
                $argumentNode['name'] &&
                (string)$argumentNode->attributes('xsi', true)['type'] &&
                (string)$argumentNode->attributes('xsi', true)['type'] === 'object' &&
                str_contains((string)$argumentNode, 'ViewModel')
            ) {
                $camelCaseNameGetter = 'get' . ucfirst($this->phtmlUtils->snakeToCamelCase((string)$argumentNode['name']));
                $viewModelClasses[] = $camelCaseNameGetter;
            }
        }
        return $viewModelClasses;
    }


    /**
     * @param string $block
     * @return array
     * @throws \ReflectionException
     */
    private function getMethodsFromBlock(string $block): array
    {
        $methods = [];
        $reflection = new \ReflectionClass($block);
        foreach ($reflection->getMethods() as $method) {
            /**
             * We only want to get public methods that are getters
             */
            if (
                $method->isPublic() &&
                (str_starts_with($method->getName(), 'get') || str_starts_with($method->getName(), 'is'))
                && !str_contains($method->getName(), 'Child')
            ) {
                $methods[] = $method->getName();
            }
        }
        return $methods;
    }

    /**
     * Using different patterns, this method checks if the phtml file is using block to retrieve data or configuration.
     * It also checks if the file is using $this instead of $block.
     *
     * @param array $files
     * @param array $methods
     * @throws FileSystemException
     */
    private function checkPhtmlFiles(array $files, array $methods = []): void
    {
        $viewModels = $files['viewModels'];
        foreach ($files['files'] as $file) {
            $content = $this->driver->fileGetContents($file);
            /**
             * Checks if the file is using $block->getSomething() or $block->isSomething()
             */
            $pattern = '/\$block->(get|is)(\w+)\(\)/';
            $this->checkDataCrunch($content, $methods, $pattern, $file, $viewModels);
            /**
             * Use of $this is not recommended in a block class
             */
            if ($this->phtmlUtils->getThisInPhtmlFile($content)) {
                $this->results['hasErrors'] = true;
                $this->results['errors']['useOfThisInsteadOfBlock']['files'][] = $file;
                $this->addErroneousFile($file, 3);
            }
            /**
             * Checks if the file is using $this->getSomething()
             */
            $pattern = '/\$this->(get|is)(\w+)\(\)/';
            $this->checkDataCrunch($content, $methods, $pattern, $file, $viewModels);
        }
    }

    /**
     * Checks if the file is using block to retrieve data or configuration using a pattern
     *
     * @param string $content
     * @param array $methods
     * @param string $pattern
     * @param string $file
     * @return void
     */
    private function checkDataCrunch(string $content, array $methods, string $pattern, string $file, array $viewModels): void
    {
        preg_match_all($pattern, $content, $matches);
        if ($matches !== []) {
            foreach ($matches[0] as $match) {
                if (str_contains($match, 'getJsLayout')) {
                    continue;
                }
                foreach ($viewModels as $viewModel) {
                    if (str_contains($match, (string) $viewModel)) {
                        continue 2;
                    }
                }
                $this->results['hasErrors'] = true;
                if ($methods !== [] && !in_array($match, $methods)) {
                    if (isset($this->results['errors']['directBlockDataCrunch']['files'][$file][$match])) {
                        $this->results['errors']['directBlockDataCrunch']['files'][$file][$match]++;
                    } else {
                        $this->results['errors']['directBlockDataCrunch']['files'][$file][$match] = 1;
                    }
                    $this->addErroneousFile($file, 5);
                } else {
                    if (isset($this->results['warnings']['dataCrunchInPhtml']['files'][$file][$match])) {
                        $this->results['warnings']['dataCrunchInPhtml']['files'][$file][$match]++;
                    } else {
                        $this->results['warnings']['dataCrunchInPhtml']['files'][$file][$match] = 1;
                    }
                    $this->addErroneousFile($file, 2);
                }
            }
        }
    }
}
