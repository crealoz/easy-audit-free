<?php

namespace Crealoz\EasyAudit\Api\Processor;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
interface AuditProcessorInterface
{
    /**
     * @param $input
     */
    public function run(): void;

    /**
     * @return string
     */
    public function getProcessorName(): string;

    /**
     * Get a tag without any spaces or special characters
     * @return string
     */
    public function getProcessorTag(): string;

    /**
     * @return array
     */
    public function getResults(): array;

    public function getAuditSection(): string;

    public function getErroneousFiles(): array;

    public function prepopulateResults(): void;

    public function hasErrors(): bool;
}
