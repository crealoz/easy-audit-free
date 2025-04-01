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

namespace Crealoz\EasyAudit\Api\Data;

interface ResultInterface
{
    const ID = 'result_id';
    const REQUEST_ID = 'request_id';
    const SUMMARY = 'summary';
    const SEVERITY_ID = 'severity_id';
    const ENTRIES = 'entries';
    const PROCESSOR = 'processor';
    const TITLE = 'title';
    const SEVERITY = 'severity';
    const PR_ENABLED = 'pr_enabled';
    const PR_STATUS = 'pr_status';
    const DIFF = 'diff';
    const QUEUE_ID = 'queue_id';

    /**
     * @return int
     */
    public function getResultId(): int;

    /**
     * @param int $result_id
     * @return self
     */
    public function setResultId(int $result_id): self;

    /**
     * @return int
     */
    public function getRequestId(): int;

    /**
     * @param int $request_id
     * @return self
     */
    public function setRequestId(int $request_id): self;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * @return string
     */
    public function getSummary(): string;

    /**
     * @param string $summary
     * @return self
     */
    public function setSummary(string $summary): self;

    /**
     * @return int
     */
    public function getSeverityId(): int;

    /**
     * @param int $severity_id
     * @return self
     */
    public function setSeverityId(int $severity_id): self;

    /**
     * @return SeverityInterface
     */
    public function getSeverity(): SeverityInterface;

    /**
     * @param SeverityInterface $severity
     * @return self
     */
    public function setSeverity(SeverityInterface $severity): self;

    /**
     * @return EntryInterface[]
     */
    public function getEntries(): array;

    /**
     * @param array $entries
     * @return self
     */
    public function setEntries(array $entries): self;

    /**
     * @return string
     */
    public function getProcessor(): string;

    /**
     * @param string $group
     * @return self
     */
    public function setProcessor(string $group): self;

    /**
     * @param EntryInterface $entry
     * @return self
     */
    public function addEntry(EntryInterface $entry): self;

    /**
     * Get the PR enabled
     *
     * @return int
     */
    public function getPrEnabled(): int;

    /**
     * Set the PR enabled
     *
     * @param int $pr_enabled
     * @return self
     */
    public function setPrEnabled(int $pr_enabled): self;

    /**
     * Get the PR status
     *
     * @return string|null
     */
    public function getPrStatus(): ?string;

    /**
     * Set the PR status
     *
     * @param string $pr_status
     * @return self
     */
    public function setPrStatus(string $pr_status): self;

    /**
     * Get the PR file
     *
     * @return string|null
     */
    public function getDiff(): ?string;

    /**
     * Set the PR file
     *
     * @param string $diff
     * @return self
     */
    public function setDiff(string $diff): self;

    /**
     * Get the queue id
     *
     * @return string|null
     */
    public function getQueueId(): ?string;

    /**
     * Set the queue id
     *
     * @param string $queue_id
     * @return self
     */
    public function setQueueId(string $queue_id): self;
}
