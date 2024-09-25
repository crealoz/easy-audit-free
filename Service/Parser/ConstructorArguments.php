<?php

namespace Crealoz\EasyAudit\Service\Parser;

class ConstructorArguments
{


    /**
     * @param \ReflectionClass $class
     * @return \ReflectionNamedType[]|\ReflectionUnionType[]|\ReflectionIntersectionType[]|[]
     */
    public function execute(\ReflectionClass $class)
    {
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return [];
        }
        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = $parameter->getType();
        }
        return $arguments;
    }
}