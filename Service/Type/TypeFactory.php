<?php

namespace Crealoz\EasyAudit\Service\Type;

class TypeFactory
{
    public function __construct(
        protected readonly \Magento\Framework\ObjectManagerInterface $objectManager,
        protected readonly array $typeMapping,
    )
    {
    }

    public function create(string $type): TypeInterface
    {
        if (!isset($this->typeMapping[$type])) {
            throw new \InvalidArgumentException("Unknown type: $type");
        }
        $object = $this->objectManager->get($this->typeMapping[$type]);
        if (!$object instanceof TypeInterface) {
            throw new \InvalidArgumentException("Could not create an object of type $type. It does not implement
             TypeInterface. Allowed types: " . implode(", ", array_keys($this->typeMapping)));
        }
        return $this->objectManager->get($this->typeMapping[$type]);
    }
}