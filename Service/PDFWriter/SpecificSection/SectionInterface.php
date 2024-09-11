<?php

namespace Crealoz\EasyAudit\Service\PDFWriter\SpecificSection;

use Crealoz\EasyAudit\Service\PDFWriter;

interface SectionInterface
{
    public function writeSection(PDFWriter $pdfWriter, array $subresults): void;
}