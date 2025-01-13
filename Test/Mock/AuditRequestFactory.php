<?php

namespace Crealoz\EasyAudit\Model;

class AuditRequestFactory {
    public function create(array $data = []) {
        return new AuditRequest($data);
    }
}