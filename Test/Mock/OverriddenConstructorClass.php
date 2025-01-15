<?php

namespace Crealoz\EasyAudit\Test\Mock;

class OverriddenConstructorClass extends BaseConstructorClass
{
    private $argument;

    public function __construct($arg1, $arg2) {
        parent::__construct();
        $this->argument = $arg1 . $arg2;
    }

    public function getArgument() {
        return $this->argument;
    }
}