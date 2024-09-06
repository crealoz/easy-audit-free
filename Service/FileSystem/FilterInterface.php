<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

interface FilterInterface
{
    public function retrieve(): array;
}