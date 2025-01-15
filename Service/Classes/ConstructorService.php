<?php

namespace Crealoz\EasyAudit\Service\Classes;

class ConstructorService
{

    /**
     * @throws \ReflectionException
     */
    public function isConstructorOverridden(string $className): bool
    {
        $reflectionClass = new \ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            // No constructor defined
            return false;
        }

        // Check if the class extends another class
        if ($reflectionClass->getParentClass() === false) {
            return false;
        }

        // Check if the constructor is defined in the class itself
        return $constructor->getDeclaringClass()->getName() === $className;
    }
}