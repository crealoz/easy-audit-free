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

namespace Crealoz\EasyAudit\Model\ResourceModel;


use Crealoz\EasyAudit\Api\Data\ResultInterface;
use Crealoz\EasyAudit\Model\Result\Entry\SubEntryFactory;
use Crealoz\EasyAudit\Model\Result\EntryFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Result extends AbstractDb
{
    private array $entries = [];

    private array $existingEntries = [];


    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        protected readonly EntryFactory                   $entryFactory,
        protected readonly SubEntryFactory                $subEntryFactory,
                                                          $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('crealoz_easyaudit_result', ResultInterface::ID);
    }

    protected function retrieveEntriesFromDb(AbstractModel $object)
    {
        /** @var ResultInterface $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('crealoz_easyaudit_result_entry'))
            ->where('result_id = ?', $object->getResultId());
        $entries = $this->getConnection()->fetchAll($select);
        foreach ($entries as $entry) {
            $entry['subentries'] = $this->retrieveSubEntriesFromDb($entry['entry_id']);
        }
        return $entries;
    }

    /**
     * Retrieve subentries from db
     *
     * @param int $entryId
     * @return array
     */
    private function retrieveSubEntriesFromDb($entryId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('crealoz_easyaudit_result_subentry'))
            ->where('entry_id = ?', $entryId);
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Delete all entries
     *
     * @param AbstractModel $object
     */
    protected function deleteAllEntries(AbstractModel $object)
    {
        /** @var ResultInterface $result */
        $this->getConnection()->delete(
            $this->getTable('crealoz_easyaudit_result_entry'),
            ['result_id = ?' => $object->getResultId()]
        );
    }

    /**
     * Get entries
     *
     * @param AbstractModel $object
     * @return array
     */
    protected function getEntries(AbstractModel $object)
    {
        /** @var ResultInterface $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('crealoz_easyaudit_result_entry'))
            ->where('result_id = ?', $object->getResultId());
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Set entries
     *
     * @param AbstractModel $object
     * @param array $entries
     */
    protected function setEntries(AbstractModel $object, array $entries)
    {
        /** @var ResultInterface $object */
        $object->setEntries($entries);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var ResultInterface $object */
        $this->entries = $object->getEntries();
        $this->existingEntries = $this->retrieveEntriesFromDb($object);
        return parent::_beforeSave($object);
    }

    protected function _afterSave(AbstractModel $object)
    {
        $this->manageEntries($object);
        return parent::_afterSave($object);
    }

    /**
     * Delete entries and add new ones
     *
     * @param AbstractModel $result
     * @return Result
     */
    protected function manageEntries(AbstractModel $result)
    {
        /** @var ResultInterface $result */
        /**
         * If entry is in the existing entries but not in the entries, it should be deleted.
         * If entry is in the entries but not in the existing entries, it should be added.
         */
        $this->deleteEntries();
        $this->insertEntries($result);
        return parent::_afterSave($result);
    }

    protected function _afterDelete(AbstractModel $object)
    {
        /** @var ResultInterface $result */
        $this->deleteAllEntries($object);
        return parent::_afterDelete($object);
    }

    /**
     * Delete entries
     */
    protected function deleteEntries()
    {
        $entriesToDelete = [];
        foreach ($this->existingEntries as $existingEntry) {
            $found = false;
            foreach ($this->entries as $entry) {
                if ($entry['entry_id'] == $existingEntry['entry_id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $entriesToDelete[] = $existingEntry['entry_id'];
            }
        }
        if ($entriesToDelete === []) {
            return;
        }
        $this->getConnection()->delete(
            $this->getTable('crealoz_easyaudit_result_entry'),
            ['entry_id IN (?)' => $entriesToDelete]
        );
        foreach ($entriesToDelete as $entryId) {
            $this->getConnection()->delete(
                $this->getTable('crealoz_easyaudit_result_subentry'),
                ['entry_id = ?' => $entryId]
            );
        }
    }

    /**
     * Add entries
     *
     * @param AbstractModel $result
     */
    protected function insertEntries(AbstractModel $result)
    {
        $entriesToAdd = [];
        foreach ($this->entries as $entry) {
            $found = false;
            foreach ($this->existingEntries as $existingEntry) {
                if ($entry['entry_id'] == $existingEntry['entry_id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $entriesToAdd[] = [
                    'result_id' => $result->getResultId(),
                    'type_id' => $entry['type_id'],
                    'entry' => $entry['entry'],
                    'parent_id' => $entry['parent_id'] ?? null,
                    'subentries' => $entry['subentries'] ?? []
                ];
            }
        }
        if ($entriesToAdd === []) {
            return;
        }
        $this->getConnection()->insertMultiple(
            $this->getTable('crealoz_easyaudit_result_entry'),
            $entriesToAdd
        );
        foreach ($entriesToAdd as $entry) {
            $entryId = $this->getConnection()->lastInsertId();
            $subEntries = $entry['subentries'];
            foreach ($subEntries as $subEntry) {
                $this->getConnection()->insert(
                    $this->getTable('crealoz_easyaudit_result_subentry'),
                    [
                        'entry_id' => $entryId,
                        'subentry' => $subEntry['subentry']
                    ]
                );
            }
        }
    }

    /**
     * @param AbstractModel $object
     * @return Result
     */
    protected function _afterLoad(AbstractModel $object)
    {
        /** @var ResultInterface $object */
        $entries = $this->getEntries($object);
        foreach ($entries as $entry) {
            $entryModel = $this->entryFactory->create();
            $entryModel->setData($entry);
            $subEntries = $this->retrieveSubEntriesFromDb($entry['entry_id']);
            foreach ($subEntries as $subEntry) {
                $subEntryModel = $this->subEntryFactory->create();
                $subEntryModel->setData($subEntry);
                $entryModel->addSubEntry($subEntryModel);
            }
            $object->addEntry($entryModel);
        }
        return parent::_afterLoad($object);
    }
}
