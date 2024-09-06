<?php

namespace Crealoz\EasyAudit\Service;

class ArrayTools
{
    public function recursiveArrayIntersect(array $mainArray, array $secondArray): array {
        $result = [];

        foreach ($mainArray as $key => $value) {
            if (array_key_exists($key, $secondArray)) {
                if (is_array($value) && is_array($secondArray[$key])) {
                    $result[$key] = $this->recursiveArrayIntersect($value, $secondArray[$key]);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}