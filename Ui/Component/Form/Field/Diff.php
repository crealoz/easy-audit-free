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

namespace Crealoz\EasyAudit\Ui\Component\Form\Field;

use Crealoz\EasyAudit\Api\EntryRepositoryInterface;
use Crealoz\EasyAudit\Api\SubEntryRepositoryInterface;
use Crealoz\EasyAudit\Exception\TooManyResultsException;
use Crealoz\EasyAudit\Service\PrManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Psr\Log\LoggerInterface;

class Diff extends \Magento\Ui\Component\Form\Field
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        protected readonly EntryRepositoryInterface $entryRepository,
        protected readonly SubEntryRepositoryInterface $subEntryRepository,
        protected readonly PrManager $prManager,
        protected readonly LoggerInterface $logger,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (array_key_exists('diff', $dataSource['data']['general']) && (isset($dataSource['data']['general']['queue_id']))) {
            try {
                $diff = $this->prManager->getRemoteDiff($dataSource['data']['general']['queue_id']);
                $dataSource['data']['general']['diff'] = $diff;
            } catch (NoSuchEntityException $e) {
                $this->logger->error('A diff ID was requested but there are no results for this queue ID.');
            } catch (LocalizedException $e) {
                // do nothing
            }
        }

        return $dataSource;
    }
}
