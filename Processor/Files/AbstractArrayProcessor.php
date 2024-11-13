<?php

namespace Crealoz\EasyAudit\Processor\Files;

use Crealoz\EasyAudit\Api\Processor\Audit\ArrayProcessorInterface;

abstract class AbstractArrayProcessor extends AbstractAuditProcessor implements ArrayProcessorInterface
{
    protected array $array = [];

    public function setArray(array $array): void
    {
        $this->array = $array;
    }

    public function getArray(): array
    {
        return $this->array;
    }
}