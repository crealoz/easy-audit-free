<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use Magento\Framework\ObjectManagerInterface;

class FileGetterFactory
{

    protected ObjectManagerInterface $objectManager;
    /**
     * @readonly
     */
    private array $fileGetters = [];
    public function __construct(ObjectManagerInterface $objectManager, array $fileGetters = [])
    {
        $this->objectManager = $objectManager;
        $this->fileGetters = $fileGetters;
    }

    public function create(string $type): FileGetterInterface
    {
        if (!isset($this->fileGetters[$type])) {
            throw new \InvalidArgumentException('Unknown file getter type: ' . $type);
        }
        return $this->objectManager->create($this->fileGetters[$type]);
    }
}