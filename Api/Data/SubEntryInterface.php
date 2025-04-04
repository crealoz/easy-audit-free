<?php


namespace Crealoz\EasyAudit\Api\Data;

interface SubEntryInterface 
{
    const ID = 'subentry_id';
    const ENTRY_ID = 'entry_id';
    const SUBENTRY = 'subentry';

    public function getSubentryId();
    public function setSubentryId(int $subentry_id);
    public function getEntryId();
    public function setEntryId(int $entry_id);
    public function getSubentry();
    public function setSubentry(string $subentry);
}
