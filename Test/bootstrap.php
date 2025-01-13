<?php

if (!class_exists('Crealoz\EasyAudit\Model\AuditRequestFactory')) {
    require_once __DIR__ . '/Mock/AuditRequestFactory.php';
}

if (!class_exists('Crealoz\EasyAudit\Model\Request\FileFactory')) {
    require_once __DIR__ . '/Mock/FileFactory.php';
}
