<?php

namespace Crealoz\EasyAudit\Api\FileSystem;

interface FilterInterface
{
    public function retrieve(): array;
}