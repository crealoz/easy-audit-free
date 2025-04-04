<?php


namespace Crealoz\EasyAudit\Model\Result;

use Crealoz\EasyAudit\Api\Data\TypeInterface;
use Magento\Framework\Model\AbstractModel;

class Type extends AbstractModel implements TypeInterface
{
    protected $_idFieldName = self::ID;

    /**
     * @inheritDoc
     */
    public function getTypeId(): int
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setTypeId(int $type_id): self
    {
        $this->setData(self::ID, $type_id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): self
    {
        $this->setData(self::NAME, $name);
        return $this;
    }
}
