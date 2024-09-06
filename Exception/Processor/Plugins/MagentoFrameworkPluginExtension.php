<?php

namespace Crealoz\EasyAudit\Exception\Processor\Plugins;

use Crealoz\EasyAudit\Exception\Processor\AuditProcessorException;
use Magento\Framework\Phrase;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class MagentoFrameworkPluginExtension extends AuditProcessorException
{
    private string $pluggedFile;

    public function __construct(Phrase $phrase, string $erroneousFile, string $pluggedFile, \Exception $cause = null, $code = 0)
    {
        parent::__construct($phrase, $erroneousFile, $cause, $code);
        $this->pluggedFile = $pluggedFile;
    }

    public function getPluggedFile(): string
    {
        return $this->pluggedFile;
    }
}
