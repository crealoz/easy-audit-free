<?php

namespace Crealoz\EasyAudit\Test\Unit\Service\Classes;

use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Service\Classes\ArgumentTypeChecker;

class ArgumentTypeCheckerTest extends TestCase
{
    private ArgumentTypeChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new ArgumentTypeChecker();
    }

    public function testIsArgumentModel()
    {
        $this->assertTrue($this->checker->isArgumentModel('UserModel'));
        $this->assertFalse($this->checker->isArgumentModel('User'));
    }

    public function testIsArgumentAnInterfaceOrFactory()
    {
        $this->assertTrue($this->checker->isArgumentAnInterfaceOrFactory('UserFactory'));
        $this->assertTrue($this->checker->isArgumentAnInterfaceOrFactory('UserInterface'));
        $this->assertFalse($this->checker->isArgumentAnInterfaceOrFactory('User'));
    }

    public function testIsArgumentMagentoModel()
    {
        $this->assertTrue($this->checker->isArgumentMagentoModel('Magento\Framework\Model\User'));
        $this->assertFalse($this->checker->isArgumentMagentoModel('User'));
    }

    public function testIsArgumentBasicType()
    {
        $this->assertTrue($this->checker->isArgumentBasicType('string'));
        $this->assertFalse($this->checker->isArgumentBasicType('User'));
    }

    public function testIsArgumentContext()
    {
        $this->assertTrue($this->checker->isArgumentContext('UserContext'));
        $this->assertFalse($this->checker->isArgumentContext('User'));
    }

    public function testIsArgumentStdLib()
    {
        $this->assertTrue($this->checker->isArgumentStdLib('UserStdlib'));
        $this->assertFalse($this->checker->isArgumentStdLib('User'));
    }

    public function testIsArgumentSerializer()
    {
        $this->assertTrue($this->checker->isArgumentSerializer('UserSerializer'));
        $this->assertFalse($this->checker->isArgumentSerializer('User'));
    }

    public function testIsArgumentRegistry()
    {
        $this->assertTrue($this->checker->isArgumentRegistry('Magento\Framework\Registry'));
        $this->assertFalse($this->checker->isArgumentRegistry('User'));
    }

    public function testIsArgumentSession()
    {
        $this->assertTrue($this->checker->isArgumentSession('UserSession'));
        $this->assertFalse($this->checker->isArgumentSession('User'));
    }

    public function testIsArgumentHelper()
    {
        $this->assertTrue($this->checker->isArgumentHelper('UserHelper'));
        $this->assertFalse($this->checker->isArgumentHelper('User'));
    }

    public function testIsArgumentFileSystem()
    {
        $this->assertTrue($this->checker->isArgumentFileSystem('Magento\Framework\Filesystem'));
        $this->assertFalse($this->checker->isArgumentFileSystem('User'));
    }

    public function testIsArgumentCollection()
    {
        $this->assertTrue($this->checker->isArgumentCollection('UserCollection'));
        $this->assertFalse($this->checker->isArgumentCollection('User'));
    }

    public function testIsArgumentRepository()
    {
        $this->assertTrue($this->checker->isArgumentRepository('UserRepository'));
        $this->assertFalse($this->checker->isArgumentRepository('User'));
    }

    public function testIsArgumentGenerator()
    {
        $this->assertTrue($this->checker->isArgumentGenerator('UserGenerator'));
        $this->assertFalse($this->checker->isArgumentGenerator('User'));
    }

    public function testIsArgumentResourceModel()
    {
        $this->assertTrue($this->checker->isArgumentResourceModel('UserResourceModel'));
        $this->assertFalse($this->checker->isArgumentResourceModel('User'));
    }
}