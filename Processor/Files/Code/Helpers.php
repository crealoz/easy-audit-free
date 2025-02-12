<?php

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractFileProcessor;
use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Helpers extends AbstractFileProcessor implements FileProcessorInterface
{
    public const ORDER = 50;

    public const TAG = 'helpers';

    public function __construct(
        AuditStorage $auditStorage,
        private readonly FileGetterFactory $fileGetterFactory,
        protected readonly DriverInterface $driver,
        protected readonly File $io,
        private readonly ClassNameGetter $classNameGetter,
        private readonly ModuleTools $moduleTools
    )
    {
        parent::__construct($auditStorage);
    }

    public function getProcessorName(): string
    {
        return __('Helpers');
    }

    public function getAuditSection(): string
    {
        return __('PHP');
    }

    private array $helpersInPhtmlFiles = [];

    private array $ignoredHelpers = [
        'Magento\Customer\Helper\Address',
        'Magento\Tax\Helper\Data',
        'Magento\Msrp\Helper\Data',
        'Magento\Catalog\Helper\Output',
        '\Magento\Directory\Helper\Data'
    ];

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'extensionOfAbstractHelper' => $this->getExtensionOfAbstractHelperEntry(),
                'helpersInsteadOfViewModels' => $this->getHelpersInsteadOfViewModelsEntry()
            ],
            'warnings' => [
                'couldNotReadFile' => $this->getCouldNotReadFileEntry(),
                'couldNotParseCorrectlyContent' => $this->getCouldNotParseCorrectlyContentEntry()
            ],
            'suggestions' => []
        ];
    }

    private function getExtensionOfAbstractHelperEntry(): array
    {
        $title = __('Extension of Abstract Helper');
        $explanation = __('Helper class must not extend Magento\Framework\App\Helper\AbstractHelper');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    private function getHelpersInsteadOfViewModelsEntry(): array
    {
        $title = __('Helpers Instead of View Models');
        $explanation = __('Helpers should not be used as View Models');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'manageHelperInsteadOfViewModel'
        ];
    }

    private function getCouldNotReadFileEntry(): array
    {
        $title = __('Could not read file');
        $explanation = __('Could not read file');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
        ];
    }

    private function getCouldNotParseCorrectlyContentEntry(): array
    {
        $title = __('Could not parse correctly content');
        $explanation = __('Could not parse correctly content');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
        ];
    }

    public function run(): void
    {
        $moduleName = $this->moduleTools->getModuleNameByAnyFile($this->getFile());
        if ($this->auditStorage->isModuleIgnored($moduleName)) {
            return;
        }
        if ($this->helpersInPhtmlFiles === []){
            $this->retrieveHelpersInPhtml();
        }
        // First we get class name from the input that represents the file's path
        $className = $this->classNameGetter->getClassFullNameFromFile($this->getFile());

        if ($className === '') {
            return;
        }

        $reflection = new \ReflectionClass($className);
        if ($reflection->isSubclassOf('Magento\Framework\App\Helper\AbstractHelper')) {
            $this->results['hasErrors'] = true;
            $classNameWithoutOpeningSlash = ltrim($className, '\\');
            if (isset($this->helpersInPhtmlFiles[$className]) || isset($this->helpersInPhtmlFiles[$classNameWithoutOpeningSlash])) {
                $this->results['errors']['helpersInsteadOfViewModels']['files'][$className] = $this->helpersInPhtmlFiles[$className] ?? $this->helpersInPhtmlFiles[$classNameWithoutOpeningSlash];
                $this->addErroneousFile($this->getFile(), Audit::PRIORITY_AVERAGE);
            } else {
                $this->results['errors']['extensionOfAbstractHelper']['files'][] = $className;
                $this->addErroneousFile($this->getFile(), Audit::PRIORITY_HIGH);
            }
        }
    }

    public function getResults(): array
    {
        $results = parent::getResults();
        foreach ($results['errors']['helpersInsteadOfViewModels']['files'] as $className => $templates) {
            $results['errors']['helpersInsteadOfViewModels']['files'][$className]['usageCount'] = 0;
            foreach ($templates as $key => $template) {
                if (!isset($results['errors']['helpersInsteadOfViewModels']['files'][$className][$template])) {
                    $results['errors']['helpersInsteadOfViewModels']['files'][$className][$template] = 1;
                } else {
                    $results['errors']['helpersInsteadOfViewModels']['files'][$className][$template]++;
                }
                $results['errors']['helpersInsteadOfViewModels']['files'][$className]['usageCount']++;
                unset($results['errors']['helpersInsteadOfViewModels']['files'][$className][$key]);
            }
        }
        return $results;
    }

    protected function retrieveHelpersInPhtml(): void
    {
        $phtmlFilesGetter = $this->fileGetterFactory->create('phtml');
        $phtmlFiles = $phtmlFilesGetter->execute();
        foreach ($phtmlFiles as $phtmlFile) {
            try {
                $this->getHelpersFromPhtml($phtmlFile);
            } catch (FileSystemException $e) {
                $this->results['hasErrors'] = true;
                $this->results['warnings']['couldNotReadFile']['files'][] = $phtmlFile;
            }
        }
    }

    /**
     * @throws FileSystemException
     */
    protected function getHelpersFromPhtml(string $phtmlFile): void
    {
        $content = $this->driver->fileGetContents($phtmlFile);
        $matches = [];
        preg_match_all('/\$this->helper\((.*?)\)/s', $content, $matches);
        foreach ($matches[1] as $match) {
            $className = trim($match, '\'"');
            $className = str_replace('::class', '', $className);
            if (in_array($className, $this->ignoredHelpers)) {
                continue;
            }
            // Check if the class is an alias or an import
            if (!str_contains($className, '\\')) {
                preg_match("/use (.*\\\\$className)/", $content, $importMatches);
                if ($importMatches !== []) {
                    $className = $importMatches[1];
                } else {
                    $this->results['hasErrors'] = true;
                    $this->results['warnings']['couldNotParseCorrectlyContent']['files'][$phtmlFile] = __('Looking for the class name %1', $className);
                }
            }
            if (!isset($this->helpersInPhtmlFiles[$className])) {
                $this->helpersInPhtmlFiles[$className] = [];
            }
            $this->helpersInPhtmlFiles[$className][] = $phtmlFile;
        }
    }
}