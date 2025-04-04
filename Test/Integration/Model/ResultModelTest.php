<?php

namespace Crealoz\EasyAudit\Test\Integration\Model;

use Crealoz\EasyAudit\Api\Data\EntryInterface;
use Crealoz\EasyAudit\Api\Data\SeverityInterface;
use Crealoz\EasyAudit\Model\Result;
use PHPUnit\Framework\TestCase;

class ResultModelTest extends TestCase
{
    protected $resultModel;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder(\Crealoz\EasyAudit\Model\ResourceModel\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultModel = new Result($context, $registry, $resource);
    }

    public function testSetAndGetResultId()
    {
        $testId = 123;
        $this->resultModel->setResultId($testId);
        $this->assertEquals($testId, $this->resultModel->getResultId());
    }

    public function testSetAndGetRequestId()
    {
        $testRequestId = 456;
        $this->resultModel->setRequestId($testRequestId);
        $this->assertEquals($testRequestId, $this->resultModel->getRequestId());
    }

    public function testSetAndGetSummary()
    {
        $testSummary = 'Test Summary';
        $this->resultModel->setSummary($testSummary);
        $this->assertEquals($testSummary, $this->resultModel->getSummary());
    }

    public function testSetAndGetSeverity()
    {
        $mockSeverity = $this->createMock(SeverityInterface::class);
        $this->resultModel->setSeverity($mockSeverity);
        $this->assertEquals($mockSeverity, $this->resultModel->getSeverity());
    }

    public function testSetAndGetSeverityId()
    {
        $mockSeverityId = 1;
        $this->resultModel->setSeverityId($mockSeverityId);
        $this->assertEquals($mockSeverityId, $this->resultModel->getSeverityId());
        $severity = 'not_integer';
        $this->expectException(\TypeError::class);
        $this->resultModel->setSeverityId($severity);
    }

    public function testSetAndGetPrStatus()
    {
        $testStatus = 'pending';
        $this->resultModel->setPrStatus($testStatus);
        $this->assertEquals($testStatus, $this->resultModel->getPrStatus());
    }

    public function testSetAndGetPrEnabled()
    {
        $testStatus = 1;
        $this->resultModel->setPrEnabled($testStatus);
        $this->assertEquals($testStatus, $this->resultModel->getPrEnabled());
        $testStatus = 'not_integer';
        $this->expectException(\TypeError::class);
        $this->resultModel->setPrEnabled($testStatus);
    }

    public function testSetAndGetTitle()
    {
        $testTitle = 'Test Title';
        $this->resultModel->setTitle($testTitle);
        $this->assertEquals($testTitle, $this->resultModel->getTitle());
    }

    public function testSetAndGetDiff()
    {
        $testDiff = 'Test Diff';
        $this->resultModel->setDiff($testDiff);
        $this->assertEquals($testDiff, $this->resultModel->getDiff());
    }

    public function testSetAndGetQueueId()
    {
        $testQueueId = 123;
        $this->resultModel->setQueueId($testQueueId);
        $this->assertEquals($testQueueId, $this->resultModel->getQueueId());
        $testQueueId = 'not_integer';
        $this->resultModel->setQueueId($testQueueId);
        $this->assertEquals($testQueueId, $this->resultModel->getQueueId());
    }

    public function testAddEntry()
    {
        $mockEntry1 = $this->createMock(EntryInterface::class);
        $mockEntry2 = $this->createMock(EntryInterface::class);

        $this->resultModel->addEntry($mockEntry1);
        $this->resultModel->addEntry($mockEntry2);

        $entries = $this->resultModel->getEntries();
        $this->assertCount(2, $entries);
        $this->assertContains($mockEntry1, $entries);
        $this->assertContains($mockEntry2, $entries);
    }

    public function testSetAndGetProcessor()
    {
        $testProcessor = 'test_processor';
        $this->resultModel->setProcessor($testProcessor);
        $this->assertEquals($testProcessor, $this->resultModel->getProcessor());
    }

}