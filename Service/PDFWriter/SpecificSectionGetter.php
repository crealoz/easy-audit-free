<?php

namespace Crealoz\EasyAudit\Service\PDFWriter;

use Crealoz\EasyAudit\Api\Result\SectionInterface;

class SpecificSectionGetter
{
    /**
     * @readonly
     */
    private array $specificSections = [];
    public function __construct(array $specificSections = [])
    {
        $this->specificSections = $specificSections;
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