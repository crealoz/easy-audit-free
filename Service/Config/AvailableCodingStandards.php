<?php

namespace Crealoz\EasyAudit\Service\Config;

use Crealoz\EasyAudit\Exception\Processor\FolderOrFileNotFoundException;

class AvailableCodingStandards
{
    /**
     * Retrieves a list of installed coding standards.
     *
     * @param string $root The root directory path where the coding standards should be checked.
     * @return string The output of the coding standards check, indicating installed standards.
     * @throws \RuntimeException If the process encounters an error while executing.
     */
    public function getCodingStandards(string $root): string
    {
        /**
         * Check installed coding standards, if one of them [Magento2|PSR12] is missing, skip the test
         */
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $commandStandards = 'cd ' . escapeshellarg($root) . ' && ./vendor/bin/phpcs -i';
        $processStandards = \Symfony\Component\Process\Process::fromShellCommandline($commandStandards);
        $processStandards->setTimeout(600);
        $processStandards->run();

        if (!$processStandards->isSuccessful()) {
            throw new \RuntimeException(trim($processStandards->getErrorOutput()));
        }
        return trim($processStandards->getOutput());
    }
}