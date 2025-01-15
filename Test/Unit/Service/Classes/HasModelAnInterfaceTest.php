<?php
namespace Crealoz\EasyAudit\Test\Unit\Service\Classes;

use Crealoz\EasyAudit\Service\Classes\HasModelAnInterface;
use PHPUnit\Framework\TestCase;

class HasModelAnInterfaceTest extends TestCase
{
    private HasModelAnInterface $service;

    protected function setUp(): void
    {
        $this->service = new HasModelAnInterface();
    }

    public function testExecuteWithApiInterface()
    {
        $this->assertTrue($this->service->execute(\Crealoz\EasyAudit\Test\Mock\InterfacedModel::class));
        // second call to test if the result is cached
        $this->assertTrue($this->service->execute(\Crealoz\EasyAudit\Test\Mock\InterfacedModel::class));
    }

    public function testExecuteWithoutApiInterface()
    {
        $this->assertFalse($this->service->execute(\Crealoz\EasyAudit\Test\Mock\NotInterfacedModel::class));
    }
}