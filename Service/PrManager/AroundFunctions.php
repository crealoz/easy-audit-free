<?php


namespace Crealoz\EasyAudit\Service\PrManager;

use Magento\Framework\Exception\LocalizedException;

class AroundFunctions implements BodyPreparerInterface
{
    public function prepare($result, $patchType, $relativePath = ''): array
    {
        $body = [
            'type' => $result->getProcessor(),
            'patch_type' => $patchType,
            'files' => [],
        ];
        foreach ($result->getEntries() as $entry) {
            try {
                $fileContent = new \SplFileObject($entry->getEntry());
                $path = $entry->getEntry();
            } catch (\Exception $e) {
                // try to use autoloader
                $file = new \ReflectionClass($entry->getEntry());
                $fileContent = new \SplFileObject($file->getFileName());
                $path = $file->getFileName();
            }
            $body['files'][] = [
                'path' => str_replace($relativePath, '', $path),
                'content' => $fileContent->fread($fileContent->getSize())
            ];
        }
        if (empty($body['files'])) {
            throw new LocalizedException(__('No files to send'));
        }
        return $body;
    }
}
