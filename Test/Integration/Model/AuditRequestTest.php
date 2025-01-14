<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Model\AuditRequest;
use PHPUnit\Framework\TestCase;

class AuditRequestTest extends TestCase
{
    protected $model;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new AuditRequest($context, $registry);
    }

    public function testGetUsername()
    {
        $this->model->setUsername('testuser');
        $this->assertEquals('testuser', $this->model->getUsername());
        $this->assertEquals('testuser', $this->model->getData(AuditRequest::USERNAME));
    }
}