<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

abstract class AbstractFilter implements FilterInterface
{
    protected readonly FileGetter $fileGetter;

    public function __construct(
        private readonly FileGetterFactory $fileGetterFactory,
        private readonly string $fileGetterType
    ) {

    }

    protected function getFileGetter(): FileGetterInterface
    {
        if (empty($this->fileGetter)) {
            $this->fileGetter = $this->fileGetterFactory->create($this->fileGetterType);
        }
        return $this->fileGetter;
    }

}