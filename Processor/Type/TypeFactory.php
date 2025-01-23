<?php

namespace Crealoz\EasyAudit\Processor\Type;

class TypeFactory
{
    /**
     * @readonly
     */
    protected \Magento\Framework\ObjectManagerInterface $objectManager;
    /**
     * @readonly
     */
    protected array $typeMapping;
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $typeMapping)
    {
        $this->objectManager = $objectManager;
        $this->typeMapping = $typeMapping;
    }
    public function create(string $type): TypeInterface
    {
        if (!isset($this->typeMapping[$type])) {
            throw new \InvalidArgumentException("Unknown type: $type");
        }
        $object = $this->objectManager->create($this->typeMapping[$type]);
        if (!$object instanceof TypeInterface) {
            throw new \InvalidArgumentException("Could not create an object of type $type. It does not implement
             TypeInterface. Allowed types: " . implode(", ", array_keys($this->typeMapping)));
        }
        return $object;
    }

    public function get(string $type): TypeInterface
    {
        if (!isset($this->typeMapping[$type])) {
            throw new \InvalidArgumentException("Unknown type: $type");
        }
        $object = $this->objectManager->get($this->typeMapping[$type]);
        if (!$object instanceof TypeInterface) {
            throw new \InvalidArgumentException("Could not create an object of type $type. It does not implement
             TypeInterface. Allowed types: " . implode(", ", array_keys($this->typeMapping)));
        }
        return $object;
    }
}