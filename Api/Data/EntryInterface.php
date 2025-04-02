<?php


namespace Crealoz\EasyAudit\Api\Data;

interface EntryInterface
{

    const ID = 'entry_id';
    const TYPE_ID = 'type_id';
    const RESULT_ID = 'result_id';
    const PARENT_ID = 'parent_id';
    const ENTRY = 'entry';
    const SUB_ENTRIES = 'sub_entries';
    const TYPE = 'type';
    const RESULT = 'result';

    /**
     * @return int
     */
    public function getEntryId(): int;

    /**
     * @param int $entry_id
     * @return self
     */
    public function setEntryId(int $entry_id): self;

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
    public function getParentId(): int;

    /**
     * @param int $parent_id
     * @return self
     */
    public function setParentId(int $parent_id): self;

    /**
     * @return string
     */
    public function getEntry(): string;

    /**
     * @param string $entry
     * @return self
     */
    public function setEntry(string $entry): self;

    /**
     * @return TypeInterface
     */
    public function getType(): TypeInterface;

    /**
     * @param TypeInterface $type
     * @return self
     */
    public function setType(TypeInterface $type): self;

    /**
     * @return ResultInterface
     */
    public function getResult(): ResultInterface;

    /**
     * @param ResultInterface $result
     * @return self
     */
    public function setResult(ResultInterface $result): self;

    /**
     * @return SubEntryInterface[]
     */
    public function getSubEntries(): array;

    /**
     * @param array $subEntries
     * @return self
     */
    public function setSubEntries(array $subEntries): self;

    /**
     * @param SubEntryInterface $subEntry
     * @return self
     */
    public function addSubEntry(SubEntryInterface $subEntry): self;
}
