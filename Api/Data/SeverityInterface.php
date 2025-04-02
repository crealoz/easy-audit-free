<?php


namespace Crealoz\EasyAudit\Api\Data;

interface SeverityInterface
{
    const ID = 'severity_id';
    const LEVEL = 'level';
    const COLOR = 'color';

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
     * @return string
     */
    public function getLevel(): string;

    /**
     * @param string $level
     * @return self
     */
    public function setLevel(string $level): self;

    /**
     * @return string
     */
    public function getColor(): string;

    /**
     * @param string $color
     * @return self
     */
    public function setColor(string $color): self;
}
