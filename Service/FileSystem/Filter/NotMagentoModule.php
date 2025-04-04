<?php


namespace Crealoz\EasyAudit\Service\FileSystem\Filter;

use Crealoz\EasyAudit\Service\FileSystem\AbstractFilter;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class NotMagentoModule extends AbstractFilter
{

    private array $ignoredFolder = [
        'vendor/magento/',
        'vendor/laminas/',
        'vendor/codeception/',
        'vendor/symfony/',
        'vendor/phpunit/',
        'vendor/composer/',
        'vendor/rector/',
        'vendor/sebastian/',
        'vendor/doctrine/',
        'vendor/web-token/',
        'vendor/psr/',
        'vendor/phpstan/',
        'vendor/colinmollenhour/',
        'vendor/allure-framework/',
        'vendor/christian-riesen/',
        'vendor/bin/',
        'vendor/wikimedia/',
    ];

    public function __construct(
        FileGetterFactory $fileGetterFactory,
        string $fileGetterType,
        protected readonly DriverInterface $driver,
        protected readonly Json $jsonSerializer,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct($fileGetterFactory, $fileGetterType);
    }

    /**
     * Returns an array of modules that are not declared as Magento modules in their composer.json file.
     * To accelerate the process, we ignore known folders that are not Magento modules or Magento's own modules.
     *
     * @return array|string[]
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function retrieve(): array
    {
        $files = $this->getFileGetter()->execute();
        foreach ($files as $file) {
            // Ignores files that does not exist, known folders or test files
            if (!$this->driver->isExists($file) || $this->isFileInKnownFolder($file) || str_contains((string) $file, 'Test')) {
                if (!$this->driver->isExists($file)) {
                    $folder = str_replace('composer.json', '', $file);
                    $this->ignoredFolder[] = $folder;
                }
                continue;
            }
            $json = $this->driver->fileGetContents($file);
            try {
                $data = $this->jsonSerializer->unserialize($json);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                continue;
            }
            if (!isset($data['type']) || $data['type'] !== 'magento2-module') {
                $folder = str_replace('composer.json', '', $file);
                $this->ignoredFolder[] = $folder;
            }
        }
        return $this->ignoredFolder;
    }

    /**
     * Check if the file is in a known folder that is not a Magento module
     *
     * @param string $file
     * @return bool
     */
    private function isFileInKnownFolder(string $file): bool
    {
        foreach ($this->ignoredFolder as $folder) {
            if (str_contains($file, (string) $folder)) {
                return true;
            }
        }
        return false;
    }
}
