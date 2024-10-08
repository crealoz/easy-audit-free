<?php

namespace Crealoz\EasyAudit\Service\Classes;

class HasModelAnInterface
{
    private array $modelsWithApiInterface = [];

    public function execute(string $className): bool
    {
        if (isset($this->modelsWithApiInterface[$className])) {
            return true;
        }
        $reflectionClass = new \ReflectionClass($className);
        $interfaces = $reflectionClass->getInterfaces();
        foreach ($interfaces as $interface) {
            if (str_contains($interface->getName(), 'Api')) {
                $this->modelsWithApiInterface[$className] = $interface->getName();
                return true;
            }
        }
        return false;
    }
}