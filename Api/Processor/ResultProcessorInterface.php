<?php

namespace Crealoz\EasyAudit\Api\Processor;

interface ResultProcessorInterface
{
    /**
     * Processes the results and returns the processed results
     * @param array $results
     * @return array
     */
    public function processResults(array $results): array;
}