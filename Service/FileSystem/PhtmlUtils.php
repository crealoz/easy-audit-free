<?php


namespace Crealoz\EasyAudit\Service\FileSystem;

class PhtmlUtils
{
    public function getThisInPhtmlFile($content): bool
    {
        return str_contains((string) $content, '$this');
    }

    public function snakeToCamelCase($string): string
    {
        return str_replace('_', '', ucwords((string) $string, '_'));
    }
}
