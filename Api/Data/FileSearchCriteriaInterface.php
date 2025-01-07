<?php

namespace Crealoz\EasyAudit\Api\Data;

interface FileSearchCriteriaInterface extends \Magento\Framework\Api\SearchCriteriaInterface
{

    public function setItems(array $items);
    public function getItems();
}
