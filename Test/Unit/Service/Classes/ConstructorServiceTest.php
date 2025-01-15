<?php
namespace Crealoz\EasyAudit\Test\Unit\Service\Classes;

use Crealoz\EasyAudit\Test\Mock\BaseConstructorClass;
use Crealoz\EasyAudit\Test\Mock\NoConstructorClass;
use Crealoz\EasyAudit\Test\Mock\NotOverriddenConstructorClass;
use Crealoz\EasyAudit\Test\Mock\OverriddenConstructorClass;
use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Service\Classes\ConstructorService;

class ConstructorServiceTest extends TestCase
{
    private ConstructorService $service;

    protected function setUp(): void
    {
        $this->service = new ConstructorService();
    }

    public function testIsConstructorOverridden()
    {
        // Test with a class that has an overridden constructor
        $this->assertTrue($this->service->isConstructorOverridden(OverriddenConstructorClass::class));

        // Test with a class that does not have an overridden constructor
        $this->assertFalse($this->service->isConstructorOverridden(BaseConstructorClass::class));

        // Test with a class that has no constructor defined
        $this->assertFalse($this->service->isConstructorOverridden(NotOverriddenConstructorClass::class));

        // Test with a class that has no parent class and no constructor defined
        $this->assertFalse($this->service->isConstructorOverridden(NoConstructorClass::class));
    }
}