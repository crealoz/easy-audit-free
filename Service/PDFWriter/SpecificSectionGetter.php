<?php

namespace Crealoz\EasyAudit\Service\PDFWriter;

use Crealoz\EasyAudit\Api\Result\SectionInterface;

class SpecificSectionGetter
{
    public function __construct(
        private readonly array $specificSections = []
    )
    {
    }

    /**
     * @param string $sectionName
     * @return SectionInterface
     * @throws \InvalidArgumentException
     */
    public function getSpecificSection(string $sectionName): SectionInterface
    {
        if (!isset($this->specificSections[$sectionName]) || !$this->specificSections[$sectionName] instanceof SectionInterface) {
            throw new \InvalidArgumentException("Section $sectionName not found");
        }
        return $this->specificSections[$sectionName];
    }
}