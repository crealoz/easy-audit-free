<?php


namespace Crealoz\EasyAudit\Service\PrManager;

interface BodyPreparerInterface
{
    public function prepare($result, $patchType, $relativePath = ''): array;
}
