<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use Magento\Framework\ObjectManagerInterface;

class FileGetterFactory
{

    public function __construct(
        protected ObjectManagerInterface $objectManager,
        private readonly array $fileGetters = []
    ) {

    }

    public function create(string $type): FileGetterInterface
    {
        if (!isset($this->fileGetters[$type])) {
            throw new \InvalidArgumentException('Unknown file getter type: ' . $type);
        }
        return $this->objectManager->create($this->fileGetters[$type]);
    }
}