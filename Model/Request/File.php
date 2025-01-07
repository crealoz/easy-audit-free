<?php

namespace Crealoz\EasyAudit\Model\Request;


use Crealoz\EasyAudit\Api\Data\FileInterface;

class File extends \Magento\Framework\Model\AbstractModel implements FileInterface
{

    protected $_idFieldName = self::ID;

    /**
     * @inheritdoc
     */
    public function getFileId(): int
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setFileId(int $file_id): FileInterface
    {
        $this->setData(self::ID, $file_id);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRequestId(): int
    {
        return $this->getData(self::REQUEST_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRequestId(int $request_id): FileInterface
    {
        $this->setData(self::REQUEST_ID, $request_id);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFilename(): string
    {
        return $this->getData(self::FILENAME);
    }

    /**
     * @inheritdoc
     */
    public function setFilename(string $filename): FileInterface
    {
        $this->setData(self::FILENAME, $filename);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContent(): string
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setContent(string $content): FileInterface
    {
        $this->setData(self::CONTENT, $content);
        return $this;
    }

}
