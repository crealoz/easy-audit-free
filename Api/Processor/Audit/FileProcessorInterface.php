<?php

namespace Crealoz\EasyAudit\Api\Processor\Audit;

use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;

interface FileProcessorInterface extends AuditProcessorInterface
{
    public function setFile(string $file): void;

    public function getFile(): string;
}