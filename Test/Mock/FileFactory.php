<?php

namespace Crealoz\EasyAudit\Model\Request;

class FileFactory
{
    public function create(array $data = [])
    {
        return new File($data);
    }
}