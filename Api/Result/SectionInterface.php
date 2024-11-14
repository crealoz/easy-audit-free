<?php

namespace Crealoz\EasyAudit\Api\Result;

use Crealoz\EasyAudit\Service\PDFWriter;

interface SectionInterface
{
    public function writeSection(PDFWriter $pdfWriter, array $subresults): void;

}