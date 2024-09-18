<?php

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Processor\Files\AbstractProcessor;
use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;

class BlockViewModelRatio extends AbstractProcessor implements ProcessorInterface
{

    public function getProcessorName(): string
    {
        return __('Block vs ViewModel Ratio');
    }

    public function getAuditSection(): string
    {
        return __('Code');
    }

    protected function prepopulateResults(): void
    {
        $this->results = [
            'hasErrors' => false,
            'errors' => [],
            'warnings' => [
                'blockViewModelRatio' => $this->getBVMWarningEntry(),
            ],
            'suggestions' => []
        ];

    }

    private function getBVMWarningEntry(): array
    {
        $title = __('Block vs ViewModel Ratio');
        $explanation = __('The ratio of Block files to ViewModel files is too high. This can be a sign of poor code organization.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'manageBlockVMRatio'
        ];
    }

    public function run($input)
    {
        if (!is_array($input)) {
            throw new \InvalidArgumentException('Input must be an array');
        }
        $files = $this->segregateFilesByModule($input);
        foreach ($files as $module => $moduleFiles) {
            $blockViewModelRatio = $this->getBlockViewModelRatio($moduleFiles);
            if ($blockViewModelRatio > 0.5) {
                $this->results['hasErrors'] = true;
                $this->results['warnings']['blockViewModelRatio']['files'][$module] = $blockViewModelRatio;
            }
        }
    }

    private function segregateFilesByModule(array $files): array
    {
        $segregatedFiles = [];
        foreach ($files as $file) {
            $module = $this->getModuleFromPath($file);
            if (!array_key_exists($module, $segregatedFiles)) {
                $segregatedFiles[$module] = [];
            }
            $segregatedFiles[$module][] = $file;
        }
        return $segregatedFiles;
    }

    private function getModuleFromPath(string $path): string
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        return $pathParts[2] . '_' . $pathParts[3];
    }

    private function getBlockViewModelRatio(array $files): float
    {
        $totalFiles = count($files);
        $blockFiles = [];
        foreach ($files as $file) {
            if ($this->isFileInBlockFolder($file)) {
                $blockFiles[] = $file;
            }
        }
        return count($blockFiles) / $totalFiles;
    }

    private function isFileInBlockFolder(string $file): bool
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $file);
        return $pathParts[4] === 'Block';
    }
}