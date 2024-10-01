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
        $countHigherThan10 = 0;
        $countHigherThan5 = 0;
        $fileList = [];
        foreach ($results['erroneousFiles'] as $file => $score) {
            $scope = $this->getScope($file);
            if ($score >= 10) {
                $countHigherThan10++;
            } elseif ($score >= 5) {
                $countHigherThan5++;
            } else {
                continue;
            }
            $fileList[$scope][$file] = $score;
        }

        $summary[] = __('%1 files have a score equal to or higher than 10. These files must have really bad design pattern and/or not follow coding standards. Please check them with high priority.', $countHigherThan10);
        $summary[] = __('Beside that, %2 files have a score equal to or higher than 5. These files must be checked with medium priority.', $countHigherThan5);

        $erroneousFiles = [
            'summary' => $summary,
            'files' => $fileList
        ];

        $results['introduction'][] = $erroneousFiles;

        return $results;

    }

    /**
     * Returns vendor, code or design scope based on file path, if it is not in any of these scopes, it returns 'other'
     * it uses str_contains to check if the file path contains 'vendor', 'code' or 'design'
     * @param $file
     * @return string
     */
    private function getScope($file) : string
    {
        if (str_contains($file, 'vendor')) {
            return 'vendor';
        }
        if (str_contains($file, 'code')) {
            return 'code';
        }
        if (str_contains($file, 'design')) {
            return 'design';
        }
        return 'other';
    }
}