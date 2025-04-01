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

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Processor\Files\AbstractFileProcessor;
use Crealoz\EasyAudit\Service\Audit;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * This processor checks if the ObjectManager is used directly in the code. The ObjectManager should not be used directly. Use dependency injection instead.
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 * @package Crealoz\EasyAudit\Processor\Files\Code
 */
class UseOfObjectManager extends AbstractFileProcessor implements FileProcessorInterface
{
    public const ORDER = 70;

    public const TAG = 'useOfObjectManager';


    public function __construct(
        AuditStorage $auditStorage,
        protected readonly DriverInterface $driver
    )
    {
        parent::__construct($auditStorage);
    }

    public function getProcessorName(): string
    {
        return __('Use of ObjectManager');
    }

    public function getAuditSection(): string
    {
        return __('PHP');
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [
                'useOfObjectManager' => $this->getUseOfObjectManagerEntry()
            ],
            'warnings' => [
                'uselessImport' => $this->getUselessImportEntry()
            ],
            'suggestions' => []
        ];
    }

    private function getUseOfObjectManagerEntry(): array
    {
        $title = __('Use of ObjectManager');
        $explanation = __('The ObjectManager should not be used directly. Use dependency injection instead.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => [],
        ];
    }

    private function getUselessImportEntry(): array
    {
        $title = __('Useless Import');
        $explanation = __('The ObjectManager was imported but does not seem to be used. Please remove the import.');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    /**
     * @throws FileSystemException
     */
    public function run(): void
    {
        $file = $this->getFile();
        $code = $this->driver->fileGetContents($file);
        if (str_contains($code, 'Magento\Framework\ObjectManagerInterface') || str_contains($code, 'Magento\Framework\App\ObjectManager') ) {
            if (
                (str_contains($code , 'use Magento\Framework\ObjectManagerInterface') || str_contains($code , 'use Magento\Framework\App\ObjectManager'))
                && (
                    !str_contains($code, '$this->objectManager')
                    && !str_contains($code, '->create(')
                    && !str_contains($code, '->get(')
                    && !str_contains($code, '->getInstance(')
                )
            ) {
                $this->results['hasErrors'] = true;
                $this->results['warnings']['uselessImport']['files'][] = $file;
                $this->addErroneousFile($file, Audit::PRIORITY_LOW);
            } elseif (!$this->isFactory($code)) {
                $this->results['hasErrors'] = true;
                $this->results['errors']['useOfObjectManager']['files'][] = $file;
                $this->addErroneousFile($file, Audit::PRIORITY_HIGH);
            }
        }
    }

    private function isFactory($code): bool
    {
        return preg_match('/class\s+\w*Factory\b/', (string) $code) === 1;
    }
}
