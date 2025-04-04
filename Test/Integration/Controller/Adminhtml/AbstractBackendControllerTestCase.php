<?php

namespace Crealoz\EasyAudit\Test\Integration\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\TestCase;

abstract class AbstractBackendControllerTestCase extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    /**
     * @var ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFlagMock;

    /**
     * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var Response|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var mixed
     */
    protected $controller;

    protected $backendUrl;
    protected $url;
    protected $redirectResultMock;
    protected $pageTitleMock;
    protected  $pageConfigMock;
    protected $jsonResultMock;

    /**
     * Setup method to initialize common mocks
     */
    protected function setUp(): void
    {

        // Create all necessary mocks
        $this->requestMock = $this->createMock(Http::class);

        $this->pageTitleMock = $this->createMock(Title::class);
        $this->pageConfigMock = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $this->pageConfigMock
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->addMethods(['setActiveMenu'])
            ->getMock();

        $this->resultPageMock->method('getConfig')->willReturn($this->pageConfigMock);

        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->redirectResultMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonResultMock = $this->createMock(\Magento\Framework\Controller\Result\Json::class);

        $this->resultFactoryMock
            ->method('create')
            ->willReturnCallback(function ($pageType) {
                $pageTypes = [
                    ResultFactory::TYPE_PAGE => $this->resultPageMock,
                    ResultFactory::TYPE_REDIRECT => $this->redirectResultMock,
                    ResultFactory::TYPE_JSON => $this->jsonResultMock,
                ];
                return $pageTypes[$pageType];
            });

        $this->sessionMock = $this->createMock(\Magento\Backend\Model\Session::class);
        $this->actionFlagMock = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->responseMock = new Response();
        $this->backendUrl = $this->createMock(\Magento\Backend\Helper\Data::class);
        $this->url = $this->createMock(\Magento\Backend\Model\Url::class);

        // Create context mock with all dependencies
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $this->contextMock
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock
            ->method('getHelper')
            ->willReturn($this->backendUrl);
        $this->contextMock
            ->method('getUrl')
            ->willReturn($this->url);
    }

    /**
     * Helper method to create a controller with mocked context
     *
     * @param string $controllerClass
     * @return mixed
     */
    protected function createControllerWithMockedContext(string $controllerClass, array $arguments = [])
    {
        // Merge context mock with any additional arguments
        $constructorArgs = array_merge(
            ['context' => $this->contextMock],
            $arguments
        );

        // Use Reflection to create instance with dynamic arguments
        $reflectionClass = new \ReflectionClass($controllerClass);
        return $reflectionClass->newInstanceArgs($constructorArgs);

    }

    /**
     * Common assertion to check admin resource
     *
     * @param string $controllerClass
     * @param string $expectedAdminResource
     */
    protected function assertAdminResource(string $controllerClass, string $expectedAdminResource)
    {
        $reflectionClass = new \ReflectionClass($controllerClass);

        $adminResourceConstant = $reflectionClass->getConstant('ADMIN_RESOURCE');

        $this->assertEquals($expectedAdminResource, $adminResourceConstant);
    }
}