<?php

namespace Crealoz\EasyAudit\Processor\Results;

class ErroneousFiles implements \Crealoz\EasyAudit\Processor\Results\ResultProcessorInterface
{
    /**
     * Checks results for erroneous files entries where score is superior to 5 then gets the ones where score is
     * superior to 10.
     * @param array $results
     * @return array
     */
    public function processResults(array $results): array
    {
        $scoreHigherThan10 = [];
        $scoreHigherThan5 = [];
        $fileList = [];
        foreach ($results['erroneousFiles'] as $file => $score) {
            if ($score > 10) {
                $scoreHigherThan10[] = $file;
            } elseif ($score > 5) {
                $scoreHigherThan5[] = $file;
            } else {
                continue;
            }
            $fileList[$file] = $score;
        }

        $summary = __('%1 files have a score higher than 10. These files must have really bad design pattern and/or
         not follow coding standards. Please check them with high priority. Beside that, %2 files have a score higher
          than 5. These files must be checked with medium priority.', count($scoreHigherThan10), count($scoreHigherThan5));

        $erroneousFiles = [
            'summary' => $summary,
            'files' => $fileList
        ];

        $results['introduction'][] = $erroneousFiles;

        return $results;

    }
}