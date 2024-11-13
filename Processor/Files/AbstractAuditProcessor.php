<?php

namespace Crealoz\EasyAudit\Processor\Files;

use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Exception\Processor\GeneralAuditException;

abstract class AbstractAuditProcessor implements AuditProcessorInterface
{

    protected array $results = [];

    protected array $erroneousFiles = [];

    public function hasErrors(): bool
    {
        return array_key_exists('hasErrors', $this->results) && $this->results['hasErrors'];
    }

    abstract public function run(): void;

    public function prepopulateResults(): void {
        $this->erroneousFiles = [];
    }

    /**
     * @throws GeneralAuditException
     */
    abstract public function getProcessorName(): string;

    abstract public function getAuditSection(): string;

    /**
     * @throws GeneralAuditException
     */
    public function getResults(): array
    {
        if (
            !array_key_exists('hasErrors', $this->results)
            && !array_key_exists('errors', $this->results)
            && !array_key_exists('warnings', $this->results)
            && !array_key_exists('suggestions', $this->results)
        ) {
            throw new GeneralAuditException(__('Results are malformed for processor %1. Please check the processor implementation.', $this->getProcessorName()));
        }
        return $this->results;
    }

    public function getErroneousFiles(): array
    {
        return $this->erroneousFiles;
    }

    protected function addErroneousFile(string $file, int $score): void
    {
        if (!array_key_exists($file, $this->erroneousFiles)) {
            $this->erroneousFiles[$file] = $score;
        } else {
            $this->erroneousFiles[$file] += $score;
        }

    }
}