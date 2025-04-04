<?php

namespace Crealoz\EasyAudit\Test\Integration\Controller\Adminhtml\Result;

use Crealoz\EasyAudit\Controller\Adminhtml\Result\Index as ResultIndexController;
use Crealoz\EasyAudit\Test\Integration\Controller\Adminhtml\AbstractBackendControllerTestCase;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class IndexTest extends AbstractBackendControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Prepare the result factory to return a page result
        $this->resultFactoryMock
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($this->resultPageMock);

        // Create the controller with mocked context
        $this->controller = $this->createControllerWithMockedContext(
            ResultIndexController::class
        );
    }

    /**
     * Test the basic execute method
     */
    public function testExecute()
    {

        // Verify page title
        $this->resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->createMock(\Magento\Framework\View\Page\Config::class));

        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Crealoz_EasyAudit::index');

        // Execute the controller
        $result = $this->controller->execute();

        // Assert the result is a ResultInterface
        $this->assertInstanceOf(
            ResultInterface::class,
            $result,
            "Controller should return a ResultInterface"
        );
    }

    /**
     * Test the admin resource constant
     */
    public function testAdminResourceConstant()
    {
        $this->assertAdminResource(
            ResultIndexController::class,
            'Crealoz_EasyAudit::view' // Adjust this to match your actual admin resource
        );
    }
}