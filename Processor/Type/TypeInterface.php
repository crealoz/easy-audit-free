<?php

namespace Crealoz\EasyAudit\Processor\Type;

use Symfony\Component\Console\Output\OutputInterface;

interface TypeInterface
{

    public function process(array $subTypes, string $type, OutputInterface $output = null): array;

    public function getErroneousFiles(): array;

    public function initResults(array $subTypes): void;
}