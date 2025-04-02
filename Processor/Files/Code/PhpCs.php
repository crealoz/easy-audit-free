<?php
/**
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */

namespace Crealoz\EasyAudit\Processor\Files\Code;

use Crealoz\EasyAudit\Exception\Processor\FolderOrFileNotFoundException;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractArrayProcessor;
use Crealoz\EasyAudit\Service\Config\AvailableCodingStandards;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * This processor launches the code with PHP Code Sniffer and process results
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 * @package Crealoz\EasyAudit\Processor\Files\Code
 */
class PhpCs extends AbstractArrayProcessor implements \Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface
{
    public const ORDER = 300;

    public const TAG = 'phpCs';

    private ?ProgressBar $progressBar = null;

    public function __construct(
        AuditStorage $auditStorage,
        private readonly ModuleTools $moduleTools,
        private readonly DirectoryList $directoryList,
        private readonly AvailableCodingStandards $availableCodingStandards,
    )
    {
        parent::__construct($auditStorage);
    }

    /**
     * @return void
     */
    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'phpCsErrors' => $this->getPhpCsResultsErrors()
            ],
            'warnings' => [
                'phpCsWarnings' => $this->getPhpCsResultsWarnings()
            ],
            'suggestions' => [
                'phpCsSuggestions' => $this->getPhpCsResultsSuggestions()
            ]
        ];
    }

    /**
     * Get title and explanation for PHP Code Sniffer errors
     * @return array
     */
    private function getPhpCsResultsErrors(): array
    {
        $title = __('PHP Code Sniffer Errors');
        $explanation = __('The PHP Code Sniffer has detected some errors in the code.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'advancedBlockVsVM'
        ];
    }

    /**
     * Get title and explanation for PHP Code Sniffer warnings
     * @return array
     */
    private function getPhpCsResultsWarnings(): array
    {
        $title = __('PHP Code Sniffer Warnings');
        $explanation = __('The PHP Code Sniffer has detected some warnings in the code.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'advancedBlockVsVM'
        ];
    }

    public function getPhpCsResultsSuggestions(): array
    {
        $title = __('PHP Code Sniffer could not be ran');
        $explanation = __('Some code standards were not found, most probably Magento2\'s. Please install the coding standards following instructions from https://github.com/magento/magento-coding-standard. If PSR12 is missing, please install it following instructions from https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standards-for-PHP_CodeSniffer.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
        ];
    }

    /**
     * @return string
     * @throws FolderOrFileNotFoundException
     */
    private function getRoot(): string
    {
        $root = $this->directoryList->getRoot();
        if (null === $root) {
            throw new FolderOrFileNotFoundException(__('Root directory not found.'));
        }
        return $root;
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws FolderOrFileNotFoundException
     */
    public function run(): void
    {
        $outputStandards = $this->availableCodingStandards->getCodingStandards($this->getRoot());

        $codingStandards = [
            'Magento2' => true,
            'PSR12' => true
        ];

        if (!str_contains($outputStandards, 'Magento2')) {
            unset($codingStandards['Magento2']);
            $this->results['hasErrors'] = true;
            $this->results['suggestions']['phpCsSuggestions']['files'][] = 'Magento2 coding standards are missing.';
        }

        if (!str_contains($outputStandards, 'PSR12')) {
            unset($codingStandards['PSR12']);
            $this->results['hasErrors'] = true;
            $this->results['suggestions']['phpCsSuggestions']['files'][] = 'PSR12 coding standards are missing.';
        }

        if (empty($codingStandards)) {
            return;
        }

        $codingStandards = implode(',', array_keys($codingStandards));
        foreach ($this->getArray() as $modulePath){
            $moduleName = $this->moduleTools->getModuleNameByAnyFile($modulePath);
            if (!$moduleName || $this->auditStorage->isModuleIgnored($moduleName)) {
                continue;
            }
            // Run PHP Code Sniffer with PSR12 and Magento2 standards
            $this->runPhpCs($modulePath, $codingStandards);

            $this->progressBar?->advance();
        }
    }

    /**
     * @param ProgressBar|null $progressBar
     * @return void
     */
    public function setProgressBar(?ProgressBar $progressBar): void
    {
        $this->progressBar = $progressBar;
    }

    /**
     * Checks the code with PHP Code Sniffer and stores the results in different arrays
     *
     * @param string $modulePath
     * @param string $codingStandards
     * @return void
     * @throws FolderOrFileNotFoundException|\RuntimeException
     */
    private function runPhpCs(string $modulePath, string $codingStandards): void
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $command = 'cd ' . escapeshellarg($this->getRoot()) . ' && ./vendor/bin/phpcs --standard=' . $codingStandards . ' --report-width=200 --extensions=php,phtml ' . escapeshellarg($modulePath);

        $process = \Symfony\Component\Process\Process::fromShellCommandline($command);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful() && $process->getExitCode() !== 2) {
            throw new \RuntimeException($process->getExitCodeText());
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            return;
        }

        $lines = explode("\n", $output);
        $file = '';

        foreach ($lines as $line) {
            if (str_starts_with($line, 'FOUND')) {
                continue;
            }

            if (str_starts_with($line, 'FILE:')) {
                if (preg_match('/^FILE:\s(.*)$/', $line, $matches)) {
                    $file = $matches[1];
                }
            } elseif (str_contains($line, 'ERROR')) {
                $this->manageLine($file, 'errors', $line, 'phpCsErrors', 2);
            } elseif (str_contains($line, 'WARNING')) {
                $this->manageLine($file, 'warnings', $line, 'phpCsWarnings', 1);
            }

        }

        unset($lines, $process);
        gc_collect_cycles();
    }


    private function manageLine(string $file, string $domain, string $line, string $type, int $score): void
    {
        if (preg_match('/\[\s?[x]?\s?\]\s(.*)/', $line, $matches)) {
            $this->results['hasErrors'] = true;
            $issue = $matches[1];
            if (str_contains($issue, 'Line exceeds 120 characters')) {
                $issue = 'Line exceeds 120 characters';
            }
            if (str_contains($issue, 'should not be prefixed')) {
                $issue = 'Property name should not be prefixed';
            }
            if (str_contains($issue, 'Expected at least 1 space')) {
                $issue = 'Expected at least 1 space';
            }
            if (str_contains($issue, 'Expected 1 space')) {
                $issue = 'Expected 1 space';
            }
            if (!isset($this->results[$domain][$type]['files'][$file][$issue])) {
                $this->results[$domain][$type]['files'][$file][$issue] = 1;
            } else {
                $this->results[$domain][$type]['files'][$file][$issue]++;
            }
            $this->addErroneousFile($file, $score);
        }
    }


    public function getProcessorName(): string
    {
        return __('Check PHP Code Sniffer');
    }

    public function getAuditSection(): string
    {
        return __('PHP');
    }
}
