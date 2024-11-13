<?php

namespace Crealoz\EasyAudit\Api\Processor\Audit;

use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;

interface ArrayProcessorInterface extends AuditProcessorInterface
{
    public function setArray(array $array): void;

    public function getArray(): array;
}