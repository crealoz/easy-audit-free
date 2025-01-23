<?php

namespace Crealoz\EasyAudit\Processor\Results;

use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;

class ErroneousFiles implements \Crealoz\EasyAudit\Api\Processor\ResultProcessorInterface
{

    /**
     * @readonly
     */
    private ModulePaths $modulePaths;
    public function __construct(ModulePaths $modulePaths)
    {
        $this->modulePaths = $modulePaths;
    }

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
            $filename = $this->modulePaths->stripVendorOrApp($file);
            if ($score >= 10) {
                $countHigherThan10++;
            } elseif ($score >= 5) {
                $countHigherThan5++;
            } else {
                continue;
            }
            $fileList[$scope][$filename] = $score;
        }

        if (!empty($fileList)) {
            $summary[] = __('%1 files have a score equal to or higher than 10. These files must have really bad design pattern and/or not follow coding standards. Please check them with high priority.', $countHigherThan10);
            $summary[] = __('Beside that, %1 files have a score equal to or higher than 5. These files must be checked with medium priority.', $countHigherThan5);

            $erroneousFiles = [
                'summary' => $summary,
                'files' => $fileList
            ];

            $results['introduction'][] = $erroneousFiles;
        }
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
        if (strpos($file, 'vendor') !== false) {
            return 'vendor';
        }
        if (strpos($file, 'code') !== false) {
            return 'code';
        }
        if (strpos($file, 'design') !== false) {
            return 'design';
        }
        return 'other';
    }
}