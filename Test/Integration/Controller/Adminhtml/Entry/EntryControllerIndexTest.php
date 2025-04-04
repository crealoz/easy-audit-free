<?php
namespace Crealoz\EasyAudit\Test\Integration\Controller\Adminhtml\Entry;

use Crealoz\EasyAudit\Test\Integration\Controller\Adminhtml\AbstractBackendControllerTestCase;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class EntryControllerIndexTest extends AbstractBackendControllerTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = $this->createControllerWithMockedContext(\Crealoz\EasyAudit\Controller\Adminhtml\Entry\Index::class);
    }

    /**
     * @test
     */
    public function testExecuteWithoutResultId()
    {
        // Setup: No result ID
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('result_id')
            ->willReturn(null);

        // Expect an error message to be added
        $this->messageManagerMock
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Entries cannot be viewed without a result ID.'));

        // Execute the controller
        $result = $this->controller->execute();

        // Assertions
        $this->assertInstanceOf(
            ResponseInterface::class,
            $result
        );
    }

    /**
     * @test
     */
    public function testExecuteWithResultId()
    {
        // Setup: Provide a result ID
        $testResultId = '123';
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('result_id')
            ->willReturn($testResultId);

        // Execute the controller
        $result = $this->controller->execute();

        // Assertions
        $this->assertInstanceOf(
            \Magento\Framework\View\Result\Page::class,
            $result
        );
    }

    /**
     * @test
     */
    public function testAdminResourceConstant()
    {
        $reflectionClass = new \ReflectionClass(
            \Crealoz\EasyAudit\Controller\Adminhtml\Entry\Index::class
        );

        $adminResourceConstant = $reflectionClass->getConstant('ADMIN_RESOURCE');

        $this->assertEquals('Crealoz_EasyAudit::view', $adminResourceConstant);
    }
}