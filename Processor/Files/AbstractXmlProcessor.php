<?php

namespace Crealoz\EasyAudit\Processor\Files;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;

abstract class AbstractXmlProcessor extends AbstractFileProcessor implements FileProcessorInterface
{

    protected function getContent(): \SimpleXMLElement
    {
        return simplexml_load_file($this->getFile());
    }
}