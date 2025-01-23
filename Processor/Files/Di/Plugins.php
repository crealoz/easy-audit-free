<?php

namespace Crealoz\EasyAudit\Processor\Files\Di;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToAfterPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToBeforePluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\ConfigProviderPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\MagentoFrameworkPluginExtension;
use Crealoz\EasyAudit\Exception\Processor\Plugins\SameModulePluginException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractXmlProcessor;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins\AroundToAfter;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins\AroundToBefore;
use Crealoz\EasyAudit\Processor\Files\Di\Plugins\CheckConfigProvider;
use Crealoz\EasyAudit\Service\Audit;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

class Plugins extends AbstractXmlProcessor implements FileProcessorInterface
{

    /**
     * @readonly
     */
    private AroundToAfter $aroundToAfter;
    /**
     * @readonly
     */
    private AroundToBefore $aroundToBefore;
    /**
     * @readonly
     */
    private CheckConfigProvider $checkConfigProvider;
    /**
     * @readonly
     */
    private LoggerInterface $logger;
    /**
     * @readonly
     */
    private Files $filesUtility;
    public function getProcessorName(): string
    {
        return __('Plugins');
    }

    public function getAuditSection(): string
    {
        return __('Dependency Injection (DI)');
    }

    public function __construct(
        AuditStorage $auditStorage,
        AroundToAfter       $aroundToAfter,
        AroundToBefore      $aroundToBefore,
        CheckConfigProvider $checkConfigProvider,
        LoggerInterface     $logger,
        Files               $filesUtility
    )
    {
        $this->aroundToAfter = $aroundToAfter;
        $this->aroundToBefore = $aroundToBefore;
        $this->checkConfigProvider = $checkConfigProvider;
        $this->logger = $logger;
        $this->filesUtility = $filesUtility;
        parent::__construct($auditStorage);
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

    /**
     * @return void
     * @todo ignore modules
     */
    public function run(): void
    {

        $typeNodes = $this->getContent()->xpath('//type[plugin]');
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

        if (!$this->filesUtility->classFileExists($pluggingClass)) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['nonExistentPluginFile']['files'][] = $pluggingClass;
            $this->addErroneousFile($pluggingClass, Audit::PRIORITY_AVERAGE);
        }
        try {
            $this->checkAroundToAfter($pluggingClass);
        } catch (AroundToAfterPluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['aroundToAfterPlugin']['files'][] = $e->getErroneousFile();
            $this->addErroneousFile($e->getErroneousFile(), Audit::PRIORITY_HIGH);
        }

        try {
            $this->checkAroundToBefore($pluggingClass);
        } catch (AroundToBeforePluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['aroundToBeforePlugin']['files'][] = $e->getErroneousFile();
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

    /**
     * @param string $pluggingClass
     * @param string $pluggedInClass
     * @return void
     * @throws SameModulePluginException
     */
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

    /**
     *
     * @param string $pluggingClass
     * @param string $pluggedInClass
     * @throws MagentoFrameworkPluginExtension
     */
    private function isMagentoFrameworkClass(string $pluggingClass, string $pluggedInClass): void
    {
        if (strncmp($pluggedInClass, 'Magento\\Framework\\', strlen('Magento\\Framework\\')) === 0) {
            throw new MagentoFrameworkPluginExtension(
                __('Plugin class must not be in the Magento Framework'),
                $pluggingClass, $pluggedInClass
            );
        }
    }

    /**
     * @param string $pluggingClass
     * @return void
     * @throws AroundToAfterPluginException
     */
    private function checkAroundToAfter(string $pluggingClass): void
    {
        try {
            $this->aroundToAfter->execute($pluggingClass);
        } catch (FileSystemException|\ReflectionException|AroundToBeforePluginException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param string $pluggingClass
     * @return void
     * @throws AroundToBeforePluginException
     */
    private function checkAroundToBefore(string $pluggingClass): void
    {
        try {
            $this->aroundToBefore->execute($pluggingClass);
        } catch (FileSystemException|\ReflectionException|AroundToAfterPluginException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param string $pluggedInClass
     * @param string $pluggingClass
     * @return void
     * @throws ConfigProviderPluginException
     */
    private function isCheckoutConfigProviderPlugin(string $pluggedInClass, string $pluggingClass): void
    {
        try {
            $this->checkConfigProvider->execute($pluggedInClass, $pluggingClass);
        } catch (\ReflectionException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getProcessorTag(): string
    {
        return 'plugins';
    }
}