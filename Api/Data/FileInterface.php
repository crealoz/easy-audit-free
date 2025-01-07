<?php

namespace Crealoz\EasyAudit\Api\Data;

interface FileInterface
{
    const ID = 'file_id';
    const REQUEST_ID = 'request_id';
    const FILENAME = 'filename';
    const CONTENT = 'content';

    /**
     * @return mixed
     */
    public function getFileId(): int;

    /**
     * @param int $file_id
     * @return FileInterface
     */
    public function setFileId(int $file_id): FileInterface;

    /**
     * @return mixed
     */
    public function getRequestId(): int;

    /**
     * @param int $request_id
     * @return FileInterface
     */
    public function setRequestId(int $request_id): FileInterface;

    /**
     * @return string
     */
    public function getFilename(): string;

    /**
     * @param string $filename
     * @return FileInterface
     */
    public function setFilename(string $filename): FileInterface;

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param string $content
     * @return FileInterface
     */
    public function setContent(string $content): FileInterface;
}
