<?php

namespace Crealoz\EasyAudit\Processor\Files;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;

abstract class AbstractFileProcessor extends AbstractAuditProcessor implements FileProcessorInterface
{

    protected string $file;

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    public function getFile(): string
    {
        return $this->file;
    }
}