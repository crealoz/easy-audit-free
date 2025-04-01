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

namespace Crealoz\EasyAudit\Processor\Type;

use Crealoz\EasyAudit\Api\Processor\AuditProcessorInterface;
use Crealoz\EasyAudit\Processor\Type\AbstractType;
use Crealoz\EasyAudit\Processor\Type\TypeInterface;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\ModuleList;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCs extends AbstractType implements TypeInterface
{

    const ORDER = 50;

    public function __construct(
        FileGetterFactory $fileGetterFactory,
        LoggerInterface $logger,
        private readonly ModuleList            $moduleList,
        private readonly Reader $moduleReader,
    )
    {
        parent::__construct($fileGetterFactory, $logger);
    }

    /**
     * @inheritDoc
     */
    public function process(array $subTypes, string $type, OutputInterface $output = null): array
    {
        $progressBar = null;
        $this->erroneousFiles = [];
        if ($output instanceof \Symfony\Component\Console\Output\OutputInterface) {
            $output->writeln(PHP_EOL . "Processing phpcs checks");
            /** if we are in command line, we display a bar */
            $progressBar = new ProgressBar($output);
        }
        $this->results = [];
        $firstKey = array_key_first($subTypes);
        $this->hasErrors = $this->doProcess($subTypes[$firstKey], [], $progressBar);
        $this->manageResults($subTypes[$firstKey]);
        return $this->results;
    }

    /**
     * @inheritDoc
     */
    protected function doProcess(array $processors, array $files, ProgressBar $progressBar = null): bool
    {
        $firstKey = array_key_first($processors);
        $processor = $processors[$firstKey];
        if (!$processor instanceof AuditProcessorInterface) {
            throw new \InvalidArgumentException('Processor must implement ProcessorInterface');
        }

        if (!$processor instanceof \Crealoz\EasyAudit\Processor\Files\Code\PhpCs) {
            throw new \InvalidArgumentException('Processor must implement PhpCs');
        }

        $modules = $this->moduleList->getNames();
        $checkedModules = [];
        foreach ($modules as $module) {
            $modulePath = $this->moduleReader->getModuleDir(null, $module);

            if (str_contains($modulePath, 'app/code')) {
                $checkedModules[] = $modulePath;
            }
        }
        $progressBar?->setMaxSteps(count($checkedModules));
        $progressBar?->start();
        $processor->setArray($checkedModules);
        $processor->setProgressBar($progressBar);
        $processor->run();
        $progressBar?->finish();
        return $processor->hasErrors();
    }

    protected function getProgressBarCount(array $processors, array $files): int
    {
        return count($processors);
    }
}
