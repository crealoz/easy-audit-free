<?php
namespace Crealoz\EasyAudit\Test\Integration\Controller\Adminhtml\Result;

use Crealoz\EasyAudit\Test\Integration\Controller\Adminhtml\AbstractBackendControllerTestCase;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Crealoz\EasyAudit\Service\PrManager;

class MakePrTest extends AbstractBackendControllerTestCase
{
    /**
     * @var PrManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $prManagerMock;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock PrManager
        $this->prManagerMock = $this->createMock(PrManager::class);
        $this->controller = $this->createControllerWithMockedContext(\Crealoz\EasyAudit\Controller\Adminhtml\Result\MakePr::class, [
            'prManager' => $this->prManagerMock
        ]);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testMakePrWithValidData()
    {
        // Prepare test data
        $testResultId = 123;
        $testPath = 'test/relative/path';
        $testPatchType = PrManager::PATCH_TYPE_PATCH;

        // Expect prManager to be called with these parameters
        $this->prManagerMock
            ->expects($this->once())
            ->method('sendPrRequest')
            ->with(
                $this->equalTo($testResultId),
                $this->equalTo($testPath),
                $this->equalTo($testPatchType)
            )
            ->willReturn([]);

        // Dispatch request
        $this->requestMock->method('getParam')
            ->willReturnCallback(
                function($param) use ($testResultId, $testPath, $testPatchType) {
                    if ($param === 'data') {
                        return [
                            'result_id' => $testResultId,
                            'relative_path' => $testPath,
                            'patch_type' => $testPatchType
                        ];
                    }
                    return null;
                }
            );

        $json = $this->controller->execute();

        $this->assertInstanceOf(Json::class, $json);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testMakePrWithInvalidData()
    {

        // Dispatch request
        $this->requestMock->setMethod(HttpRequest::METHOD_POST);
        $this->requestMock->setPostValue('data', []);

        $this->messageManagerMock->expects($this->once())->method('addErrorMessage')->with(__('Invalid data.'));

        $json = $this->controller->execute();

        $this->assertInstanceOf(Json::class, $json);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testMakePrWithExceptionHandling()
    {
        // Prepare test data
        $testResultId = 123;

        // Mock an exception
        $this->prManagerMock
            ->expects($this->once())
            ->method('sendPrRequest')
            ->willThrowException(new \Magento\Framework\Exception\CouldNotSaveException(__('Test exception')));

        $this->requestMock->method('getParam')
            ->willReturnCallback(
                function($param) use ($testResultId) {
                    if ($param === 'data') {
                        return [
                            'result_id' => $testResultId
                        ];
                    }
                    return null;
                }
            );

        $this->messageManagerMock->expects($this->once())->method('addErrorMessage')->with(__('An error occurred while creating the Pull Request.'));

        $json = $this->controller->execute();

        $this->assertInstanceOf(Json::class, $json);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testMakePrWithoutResultId()
    {

        $this->requestMock->method('getParam')
            ->willReturnCallback(
                function($param)  {
                    if ($param === 'data') {
                        return [
                            'test' => 'nothing'
                        ];
                    }
                    return null;
                }
            );

        $this->messageManagerMock->expects($this->once())->method('addErrorMessage')->with(__('Result ID is required.'));

        $json = $this->controller->execute();

        $this->assertInstanceOf(Json::class, $json);
    }
}