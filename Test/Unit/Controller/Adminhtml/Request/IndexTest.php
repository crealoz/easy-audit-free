<?php

namespace Crealoz\EasyAudit\Test\Unit\Controller\Adminhtml\Request;

use Crealoz\EasyAudit\Controller\Adminhtml\Request\Index;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Magento\Backend\App\Action\Context;
use Crealoz\EasyAudit\Controller\Adminhtml\Request\Create;

class IndexTest extends TestCase
{
    private $indexController;
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

        $this->indexController = new Index($this->contextMock);
    }

    public function testExecuteReturnsResultPage()
    {
        $this->resultPageMock
            ->expects($this->once())
            ->method('setActiveMenu')
            ->with(Create::ADMIN_RESOURCE);

        $result = $this->indexController->execute();

        $this->assertInstanceOf(Page::class, $result);
    }
}
