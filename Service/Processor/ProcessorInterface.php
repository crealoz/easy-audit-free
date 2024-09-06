<?php

namespace Crealoz\EasyAudit\Service\Processor;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
interface ProcessorInterface
{
    /**
     * @param $input
     */
    public function run($input): array;

    /**
     * @return string
     */
    public function getProcessorName(): string;

    /**
     * @return array
     */
    public function getResults(): array;

    public function getAuditSection(): string;
}
