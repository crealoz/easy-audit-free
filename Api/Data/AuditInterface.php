<?php

namespace Crealoz\EasyAudit\Api\Data;

interface AuditInterface
{
    const ID = 'audit_id';
    const USER = 'user';
    const DATE = 'date';
    const FILEPATH = 'filepath';
    const OVERALL_RESULT = 'overall_result';

    /**
     * @return string|null
     */
    public function getUser(): ?string;

    /**
     * @param string $user
     * @return $this
     */
    public function setUser(string $user);

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @param string $date
     * @return $this
     */
    public function setDate(string $date);

    /**
     * @return string|null
     */
    public function getFilepath(): ?string;

    /**
     * @param string $filepath
     * @return $this
     */
    public function setFilepath(string $filepath);

    /**
     * @return string|null
     */
    public function getOverallResult(): ?string;

    /**
     * @param string $overallResult
     * @return $this
     */
    public function setOverallResult(string $overallResult);

}