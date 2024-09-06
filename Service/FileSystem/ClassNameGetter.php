<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;

class ClassNameGetter
{



    public function __construct(
        protected readonly DriverInterface $driver,
        protected readonly File $io
    )
    {
    }

    /**
     * @throws FileSystemException
     */
    public function getClassName(string $filePath): string
    {
        preg_match('/\bclass\s+(\w+)/', $this->driver->fileGetContents($filePath), $matches);
        $className = $matches[1];
        // Replace directory separators with namespace separators
        return $this->getNamespace($filePath) . '\\' . $className;
    }

    /**
     * @throws FileSystemException
     */
    private function getNamespace(string $filePath): string
    {
        $fileContent = $this->driver->fileGetContents($filePath);
        preg_match('/namespace (.*);/', $fileContent, $matches);
        return $matches[1];
    }
}