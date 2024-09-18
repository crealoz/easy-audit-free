<?php

namespace Crealoz\EasyAudit\Processor\Files\Di;

use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToAfterPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToBeforePluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\ConfigProviderPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\MagentoFrameworkPluginExtension;
use Crealoz\EasyAudit\Exception\Processor\Plugins\PluginFileDoesNotExistException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\SameModulePluginException;
use Crealoz\EasyAudit\Processor\Files\AbstractProcessor;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins\AroundChecker;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins\CheckConfigProvider;
use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;
use Crealoz\EasyAudit\Service\Audit;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

class Plugins extends AbstractProcessor implements ProcessorInterface
{

    public function getProcessorName(): string
    {
        return __('Plugins');
    }

    public function getAuditSection(): string
    {
        return __('Dependency Injection (DI)');
    }

    public function __construct(
        protected AroundChecker              $aroundChecker,
        private readonly CheckConfigProvider $checkConfigProvider,
        private readonly LoggerInterface     $logger,
        private readonly Files               $filesUtility
    )
    {
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'sameModulePlugin' => $this->getSameModulePluginEntry(),
                'magentoFrameworkPlugin' => $this->getMagentoFrameworkPluginEntry(),
                'configProviderPlugin' => $this->getConfigProviderPluginEntry(),
            ],
            'warnings' => [
                'nonExistentPluginFile' => $this->getNonExistentPluginFileEntry(),
                'insufficientPermissions' => $this->getInsufficientPermissionsEntry(),
                'aroundToBeforePlugin' => $this->getAroundToBeforePluginEntry(),
                'aroundToAfterPlugin' => $this->getAroundToAfterPluginEntry(),
            ],
            'suggestions' => []
        ];
    }

    private function getSameModulePluginEntry(): array
    {
        return [
            'title' => __('Same Module Plugin'),
            'explanation' => __('Plugin class must not be in the same module as the plugged in class.'),
            'files' => []
        ];
    }

    private function getMagentoFrameworkPluginEntry(): array
    {
        return [
            'title' => __('Magento Framework Plugin'),
            'explanation' => __('Plugged in class must not be in the Magento Framework. They are called several times and can lead to performance issues.'),
            'files' => []
        ];
    }

    private function getConfigProviderPluginEntry(): array
    {
        return [
            'title' => __('Config Provider Plugin'),
            'explanation' => __('Plugin class must not be plugged on any of the config provider classes. You must define a config provider to do any modifications on the configuration.'),
            'files' => []
        ];
    }

    private function getNonExistentPluginFileEntry(): array
    {
        return [
            'title' => __('Non-existent Plugin File'),
            'explanation' => __('Plugin file does not exist.'),
            'files' => []
        ];
    }

    private function getInsufficientPermissionsEntry(): array
    {
        return [
            'title' => __('Insufficient Permissions'),
            'explanation' => __('Insufficient permissions to read file.'),
            'files' => []
        ];
    }

    private function getAroundToBeforePluginEntry(): array
    {
        return [
            'title' => __('Around to Before Plugin'),
            'explanation' => __('Around plugin should be a before plugin. Around plugins drastically decrease performances of the website and should be reserved to really specific cases.'),
            'files' => []
        ];
    }

    private function getAroundToAfterPluginEntry(): array
    {
        return [
            'title' => __('Around to After Plugin'),
            'explanation' => __('Around plugin should be an after plugin. Around plugins drastically decrease performances of the website and should be reserved to really specific cases.'),
            'files' => []
        ];
    }

    public function run($input)
    {
        if (!($input instanceof \SimpleXMLElement)) {
            throw new \InvalidArgumentException("Input must be an instance of SimpleXMLElement");
        }

        $typeNodes = $input->xpath('//type[plugin]');

        try {
            foreach ($typeNodes as $typeNode) {
                $pluginNodes = $typeNode->xpath('plugin');
                $pluggedClassName = (string)$typeNode['name'];

                foreach ($pluginNodes as $pluginNode) {
                    $pluggingClassName = (string)$pluginNode['type'];
                    $pluginDisabled = (string)$pluginNode['disabled'] ?? 'false';
                    if ($pluginDisabled === 'true') {
                        continue;
                    }
                    $this->processPlugin($pluggingClassName, $pluggedClassName);
                }
            }
        } catch (FileSystemException $e) {
            $this->results['warnings']['insufficientPermissions']['files'][] = $e->getMessage();
            $this->addErroneousFile($input, Audit::PRIORITY_HIGH);
        }
    }

    /**
     * @param $pluggingClass
     * @param $pluggedInClass
     * @return void
     */
    protected function processPlugin($pluggingClass, $pluggedInClass): void
    {
        try {
            $this->isSameModulePlugin($pluggingClass, $pluggedInClass);
        } catch (SameModulePluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['errors']['sameModulePlugin']['files'][] = $e->getErroneousFile();
            $this->addErroneousFile($e->getErroneousFile(), Audit::PRIORITY_HIGH);
        }
        try {
            $this->isMagentoFrameworkClass($pluggingClass, $pluggedInClass);
        } catch (MagentoFrameworkPluginExtension $e) {
            $this->results['hasErrors'] = true;
            if (!isset($this->results['errors']['magentoFrameworkPlugin']['files'][$e->getPluggedFile()])) {
                $this->results['errors']['magentoFrameworkPlugin']['files'][$e->getPluggedFile()] = [];
            }
            $this->results['errors']['magentoFrameworkPlugin']['files'][$e->getPluggedFile()][] = $e->getErroneousFile();
            $this->addErroneousFile($e->getErroneousFile(), Audit::PRIORITY_HIGH);
        }
        try {
            $this->checkPluginFile($pluggingClass);
        } catch (PluginFileDoesNotExistException $e) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['nonExistentPluginFile']['files'][] = $e->getErroneousFile();
            $this->addErroneousFile($e->getErroneousFile(), Audit::PRIORITY_AVERAGE);
        } catch (AroundToBeforePluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['aroundToBeforePlugin']['files'][] = $e->getErroneousFile();
            $this->addErroneousFile($e->getErroneousFile(), Audit::PRIORITY_HIGH);
        } catch (AroundToAfterPluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['aroundToAfterPlugin']['files'][] = $e->getErroneousFile();
            $this->addErroneousFile($e->getErroneousFile(), Audit::PRIORITY_HIGH);
        }
        try {
            $this->isCheckoutConfigProviderPlugin($pluggedInClass, $pluggingClass);
        } catch (ConfigProviderPluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['errors']['configProviderPlugin']['files'][] = $e->getErroneousFile();
            $this->addErroneousFile($e->getErroneousFile(), Audit::PRIORITY_HIGH);
        }
    }

    private function isSameModulePlugin(string $pluggingClass, string $pluggedInClass): void
    {
        $pluggingClassParts = explode('\\', $pluggingClass);
        $pluggedInClassParts = explode('\\', $pluggedInClass);
        if ($pluggingClassParts[0].'\\'.$pluggingClassParts[1] === $pluggedInClassParts[0].'\\'.$pluggedInClassParts[1]) {
            throw new SameModulePluginException(
                __("Plugin class must not be in the same module as the plugged in class"),
                $pluggingClass
            );
        }
    }

    private function isMagentoFrameworkClass(string $pluggingClass, string $pluggedInClass): void
    {
        if (str_starts_with($pluggedInClass, 'Magento\\Framework\\')) {
            throw new MagentoFrameworkPluginExtension(
                __('Plugin class must not be in the Magento Framework'),
                $pluggingClass, $pluggedInClass
            );
        }
    }

    private function checkPluginFile(string $pluggingClass): void
    {
        if (!$this->filesUtility->classFileExists($pluggingClass)) {
            throw new PluginFileDoesNotExistException(
                __("Plugin file does not exist: $pluggingClass"),
                $pluggingClass
            );
        }
        try {
            $this->aroundChecker->execute($pluggingClass);
        } catch (FileSystemException|\ReflectionException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function isCheckoutConfigProviderPlugin(string $pluggedInClass, string $pluggingClass): void
    {
        try {
            $this->checkConfigProvider->execute($pluggedInClass, $pluggingClass);
        } catch (\ReflectionException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}