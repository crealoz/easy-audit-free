<?php

namespace Crealoz\EasyAudit\Api\Data;

interface AuditRequestInterface
{
    const ID = 'request_id';
    const USERNAME = 'username';
    const EXECUTION_TIME = 'execution_time';
    const CREATED_AT = 'created_at';
    const REQUEST = 'request';

    const FILE_PATH = 'filepath';

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get username
     *
     * @return string|null
     */
    public function getUsername(): ?string;

    /**
     * Set username
     *
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username);

    /**
     * Get execution time
     *
     * @return string|null
     */
    public function getExecutionTime(): ?string;

    /**
     * Set execution time
     *
     * @param string $executionTime
     * @return $this
     */
    public function setExecutionTime(string $executionTime);

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt);

    /**
     * Get request
     *
     * @return string|null
     */
    public function getRequest(): ?string;

    /**
     * Set request
     *
     * @param string $request
     * @return $this
     */
    public function setRequest(string $request);

    /**
     * Get file path
     *
     * @return string|null
     */
    public function getFilePath(): ?string;

    /**
     * Set file path
     *
     * @param string $filePath
     * @return $this
     */
    public function setFilePath(string $filePath);
}