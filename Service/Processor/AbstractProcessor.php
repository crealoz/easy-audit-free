<?php

namespace Crealoz\EasyAudit\Service\Processor;

use Crealoz\EasyAudit\Exception\Processor\GeneralAuditException;

abstract class AbstractProcessor implements ProcessorInterface
{
    protected string $processorName = '';

    protected array $results = [];

    protected string $auditSection = '';

    protected array $erroneousFiles = [];

    abstract public function run($input);

    /**
     * @throws GeneralAuditException
     */
    public function getProcessorName(): string
    {
        if ($this->processorName === '') {
            throw new GeneralAuditException(__('Processor name is not set'));
        }
        return $this->processorName;
    }

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

    public function getAuditSection(): string
    {
        if ($this->auditSection === '') {
            throw new GeneralAuditException(__('Audit section is not set'));
        }
        return $this->auditSection;
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