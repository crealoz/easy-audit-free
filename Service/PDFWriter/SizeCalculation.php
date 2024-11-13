<?php

namespace Crealoz\EasyAudit\Service\PDFWriter;

class SizeCalculation
{

    public function __construct(
        private readonly \Psr\Log\LoggerInterface $logger
    )
    {

    }

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
            $size += 25;
        }
        if (isset($subsection['explanation'])) {
            $size += 15;
            $size += $this->calculateNumberOfLines(preg_replace('/\s+/', ' ', $subsection['explanation'])) * 15;
        }
        if (isset($subsection['caution'])) {
            $size += 15;
            $size += $this->calculateNumberOfLines(preg_replace('/\s+/', ' ', $subsection['caution'])) * 15;
        }
        return $size;
    }

    private function calculateNumberOfLines($text, $columnCount = 1): int
    {
        if (!is_string($text)) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            dd($text);

        }
        return ceil(strlen($text) / (130 / $columnCount));
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
        $size += $this->calculateNumberOfLines(preg_replace('/\s+/', ' ', $intro['summary'])) * 15;

        return $size;
    }

    /**
     * @param $files
     * @return int if $sizeForTwoColumns is bigger than $size, return $sizeForTwoColumns
     */
    public function getNumberOfPagesForFiles($files): int
    {
        $size = 0;
        $this->logger->info('Files count: ' . count($files));
        $sizeForTwoColumns = 0;
        if (is_string($files)) {
            $size = $this->calculateNumberOfLines($files) * 15;
            $sizeForTwoColumns = $this->calculateNumberOfLines($files, 2) * 15;
        } else {
            foreach ($files ?? [] as $key => $file) {
                if (is_array($file)) {
                    $size += $this->calculateNumberOfLines($key) * 15;
                    $sizeForTwoColumns += $this->calculateNumberOfLines($key, 2) * 15;
                    foreach ($file as $subFile) {
                        $size += $this->calculateNumberOfLines($subFile) * 15;
                        $sizeForTwoColumns += $this->calculateNumberOfLines($subFile, 2) * 15;
                    }
                } else {
                    $size += $this->calculateNumberOfLines($file) * 15;
                    $sizeForTwoColumns += $this->calculateNumberOfLines($file, 2) * 15;
                }
            }
        }
        return $sizeForTwoColumns > $size ? ceil($sizeForTwoColumns / 800) : ceil($size / 800);
    }
}