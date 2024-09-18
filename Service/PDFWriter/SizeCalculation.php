<?php

namespace Crealoz\EasyAudit\Service\PDFWriter;

class SizeCalculation
{
    public function calculateTitlePlusFirstSubsectionSize($subResults, $getFirstSection = false): int
    {
        if ($getFirstSection) {
            $subResults = $this->getFirstSection($subResults);
        }
        $size = 0;
        $size += 44;
        $size += $this->calculateSectionIntroSize(reset($subResults));
        return $size;
    }

    public function calculateSectionIntroSize($subsection): int
    {
        $size = 0;
        if (isset($subsection['title'])) {
            $size += 20;
        }
        if (isset($subsection['explanation'])) {
            $size += 10;
            $size += $this->calculateNumberOfLines(preg_replace('/\s+/', ' ', $subsection['explanation']), 9) * 15;
        }
        if (isset($subsection['caution'])) {
            $size += 10;
            $size += $this->calculateNumberOfLines(preg_replace('/\s+/', ' ', $subsection['caution']), 9) * 15;
        }
        return $size;
    }

    private function calculateNumberOfLines($text, $size): int
    {
        return ceil(strlen($text) / (130 / $size));
    }

    private function getFirstSection($subResults): array
    {
        if (isset($subResults['errors'])) {
            return $subResults['errors'];
        } elseif (isset($subResults['warnings'])) {
            return $subResults['warnings'];
        } elseif (isset($subResults['suggestions'])) {
            return $subResults['suggestions'];
        }
    }

    public function calculateIntroSize($intro): int
    {
        $size = 0;
        $size += $this->calculateNumberOfLines(preg_replace('/\s+/', ' ', $intro['summary']), 9) * 15;

        return $size;
    }
}