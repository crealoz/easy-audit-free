<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Api\Data\FileInterface;
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
        $resource = $this->getMockBuilder(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new AuditRequest($context, $registry, $resource);
    }

    public function testGetUsername()
    {
        $this->model->setUsername('testuser');
        $this->assertEquals('testuser', $this->model->getUsername());
        $this->assertEquals('testuser', $this->model->getData(AuditRequest::USERNAME));
    }

    public function testGetExecutionTime()
    {
        $this->model->setExecutionTime('10');
        $this->assertEquals('10', $this->model->getExecutionTime());
        $this->assertEquals('10', $this->model->getData(AuditRequest::EXECUTION_TIME));
    }

    public function testGetCreatedAt()
    {
        $this->model->setCreatedAt('2021-01-01 00:00:00');
        $this->assertEquals('2021-01-01 00:00:00', $this->model->getCreatedAt());
        $this->assertEquals('2021-01-01 00:00:00', $this->model->getData(AuditRequest::CREATED_AT));
    }

    public function testGetId()
    {
        $this->model->setId(1);
        $this->assertEquals(1, $this->model->getId());
    }

    public function testGetFiles()
    {
        $file = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model->setFiles([$file]);
        $this->assertEquals([$file], $this->model->getFiles());
        $this->assertEquals([$file], $this->model->getData(AuditRequest::FILES));
        $file2 = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model->addFile($file2);
        $this->assertEquals([$file, $file2], $this->model->getFiles());
    }

    public function testGetRequest()
    {
        $this->model->setRequest('testrequest');
        $this->assertEquals('testrequest', $this->model->getRequest());
        $this->assertEquals('testrequest', $this->model->getData(AuditRequest::REQUEST));
    }

}