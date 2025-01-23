<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use Crealoz\EasyAudit\Api\FileSystem\FilterInterface;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * @readonly
     */
    private FileGetterFactory $fileGetterFactory;
    /**
     * @readonly
     */
    private string $fileGetterType;
    /**
     * @readonly
     */
    protected FileGetter $fileGetter;

    public function __construct(FileGetterFactory $fileGetterFactory, string $fileGetterType)
    {
        $this->fileGetterFactory = $fileGetterFactory;
        $this->fileGetterType = $fileGetterType;
    }

    protected function getFileGetter(): FileGetterInterface
    {
        if (empty($this->fileGetter)) {
            $this->fileGetter = $this->fileGetterFactory->create($this->fileGetterType);
        }
        return $this->fileGetter;
    }

}