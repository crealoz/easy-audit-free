<?php

namespace Crealoz\EasyAudit\Service\Type;

use Symfony\Component\Console\Output\OutputInterface;

interface TypeInterface
{

    public function process(array $subTypes, string $type, OutputInterface $output = null): array;
}