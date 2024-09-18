<?php

namespace Crealoz\EasyAudit\Processor\Results;

interface ResultProcessorInterface
{
    /**
     * Processes the results and returns the processed results
     * @param array $results
     * @return array
     */
    public function processResults(array $results): array;
}