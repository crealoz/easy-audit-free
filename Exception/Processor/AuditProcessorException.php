<?php

namespace Crealoz\EasyAudit\Exception\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class AuditProcessorException extends LocalizedException
{
    private readonly string $erroneousFile;

    public function __construct(Phrase $phrase, string $erroneousFile, \Exception $cause = null, $code = 0)
    {
        parent::__construct($phrase, $cause, $code);
        $this->erroneousFile = $erroneousFile;
    }

    public function getErroneousFile(): string
    {
        return $this->erroneousFile;
    }
}
