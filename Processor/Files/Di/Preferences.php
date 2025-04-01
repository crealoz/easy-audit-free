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

namespace Crealoz\EasyAudit\Processor\Files\Di;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Processor\Files\AbstractXmlProcessor;
use Crealoz\EasyAudit\Service\Audit;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Preferences extends AbstractXmlProcessor implements FileProcessorInterface
{
    public const ORDER = 30;

    public const TAG = 'preferences';

    private array $existingPreferences = [];

    public function getProcessorName(): string
    {
        return __('Preferences');
    }

    public function getAuditSection(): string
    {
        return __('Dependency Injection (DI)');
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->existingPreferences = [];
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'multiplePreferences' => $this->getMultiplePreferencesEntry()
            ],
            'warnings' => []
        ];
    }

    private function getMultiplePreferencesEntry(): array
    {
        $title = __('Multiple Preferences');
        $explanation = __('Multiple preferences found for the same file. This can lead to unexpected behavior. Please remove the duplicate preferences or check that sequence is done correctly in module declaration.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
            'specificSections' => 'manageMultiplePreferences'
        ];
    }

    public function run(): void
    {
        $preferences = $this->getContent()->xpath('//preference');
        foreach ($preferences as $preference) {
            $preferenceFor = (string)$preference['for'];
            $preferenceType = (string)$preference['type'];
            if (array_key_exists($preferenceFor, $this->existingPreferences)) {
                $this->results['hasErrors'] = true;
                $this->existingPreferences[$preferenceFor][] = $preferenceType;
                $this->results['errors']['multiplePreferences']['files'][$preferenceFor] = $this->existingPreferences[$preferenceFor];
                $this->addErroneousFile($this->getFile(), Audit::PRIORITY_LOW);
            } else {
                $this->existingPreferences[$preferenceFor] = [$preferenceType];
            }
        }
    }
}
