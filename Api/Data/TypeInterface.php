<?php


namespace Crealoz\EasyAudit\Api\Data;

interface TypeInterface
{
    const ID = 'type_id';
    const NAME = 'name';

    /**
     * @return int
     */
    public function getTypeId(): int;

    /**
     * @param int $type_id
     * @return self
     */
    public function setTypeId(int $type_id): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self;
}
