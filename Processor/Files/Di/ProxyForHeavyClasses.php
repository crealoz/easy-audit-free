<?php


namespace Crealoz\EasyAudit\Processor\Files\Di;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractFileProcessor;
use Crealoz\EasyAudit\Service\Classes\ArgumentTypeChecker;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Magento\Framework\Exception\FileSystemException;

class ProxyForHeavyClasses extends AbstractFileProcessor implements FileProcessorInterface
{
    public const ORDER = 40;

    public const TAG = 'proxyForHeavyClasses';

    public function __construct(
        AuditStorage $auditStorage,
        private readonly ClassNameGetter $classNameGetter,
        private readonly \Magento\Framework\ObjectManager\DefinitionInterface $definitions,
        private readonly ArgumentTypeChecker $argumentTypeChecker,
        private readonly ModulePaths $modulePaths
    )
    {
        parent::__construct($auditStorage);
    }

    public function getProcessorName(): string
    {
        return __('Proxy for heavy classes');
    }

    public function getAuditSection(): string
    {
        return __('Dependency Injection (DI)');
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'noProxyUsedForHeavyClasses' => $this->getNoProxyUsedEntry()
            ],
            'warnings' => []
        ];
    }

    private function getNoProxyUsedEntry(): array
    {
        $title = __('No Proxy used for heavy classes');
        $explanation = __('Some classes such as Session are heavy and should be injected through a proxy. This is to avoid performance issues when the class is instantiated. Doing so will improve performances especially when the class is not necessarily used.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
        ];
    }

    public function run(): void
    {
        // First we get class name from the input that represents the file's path
        try {
            $className = $this->classNameGetter->getClassFullNameFromFile($this->getFile());
        } catch (NotAClassException|FileSystemException $e) {
            return;
        }

        $arguments = $this->definitions->getParameters($className);
        if (empty($arguments)) {
            return;
        }
        foreach ($arguments as $argument) {
            if (!is_array($argument) || count($argument) < 2 || !is_string($argument[1])) {
                continue;
            }
            $argumentName = $argument[1];
            if ($this->argumentTypeChecker->isArgumentSession($argumentName)) {
                $this->checkProxyUsage($this->getFile(), $className, $argumentName);
            }
        }
    }

    private function checkProxyUsage($input, $className, $argumentName)
    {
        $moduleXmlPath = $this->modulePaths->getDiXml($input, $this->classNameGetter->isVendorClass($className));
        $foundProxy = false;
        foreach ($moduleXmlPath as $xmlPath) {
            $xml = simplexml_load_file($xmlPath);
            $proxies = $xml->xpath("//type[@name='$className']//argument[@name='$argumentName'/Proxy]");
            if (count($proxies) !== 0) {
                $foundProxy = true;
                break;
            }
        }
        if (!$foundProxy) {
            $this->results['hasErrors'] = true;
            $this->results['errors']['noProxyUsed']['files'][$argumentName][] = $className;
        }
    }
}
