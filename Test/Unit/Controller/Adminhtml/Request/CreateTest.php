<?php

namespace Crealoz\EasyAudit\Test\Unit\Controller\Adminhtml\Request;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Crealoz\EasyAudit\Controller\Adminhtml\Request\Create;

class CreateTest extends TestCase
{
    private $createController;
    private $resultFactoryMock;
    private $resultPageMock;
    private $contextMock;

    protected function setUp(): void
    {
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->addMethods(['setActiveMenu'])
            ->getMock();

        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultFactoryMock
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($this->resultPageMock);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->createController = new Create($this->contextMock);
    }

    public function testExecuteReturnsResultPage()
    {
        $this->resultPageMock
            ->expects($this->once())
            ->method('setActiveMenu')
            ->with(Create::ADMIN_RESOURCE);

        $result = $this->createController->execute();

        $this->assertInstanceOf(Page::class, $result);
    }
}
