<?php

namespace Crealoz\EasyAudit\Test\Integration\Controller\Adminhtml\Request;

use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Controller\Adminhtml\Request\Save;
use Crealoz\EasyAudit\Model\AuditRequest;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;

class SaveTest extends TestCase
{
    private $saveController;
    private $contextMock;
    private $auditRequestFactoryMock;
    private $auditRequestRepositoryMock;
    private $authSessionMock;
    private $serializerMock;
    private $messageManagerMock;

    protected function setUp(): void
    {
        // Create a mock for the audit request factory
        $this->auditRequestFactoryMock = $this->getMockBuilder('\Crealoz\EasyAudit\Model\AuditRequestFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->auditRequestFactoryMock->method('create')->willReturn($this->createMock(AuditRequest::class));

        // Create a mock for the audit request repository
        $this->auditRequestRepositoryMock = $this->createMock(AuditRequestRepositoryInterface::class);
        $counter = 0;
        $this->auditRequestRepositoryMock->method('save')->willReturnCallback(function () use (&$counter) {
            $counter++;
            if ($counter > 1) {
                throw new LocalizedException(__('an error occurred'));
            }
            return null;
        });

        // Create a mock for the auth session
        $this->authSessionMock = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getUser'])
            ->getMock();
        $userMock = $this->createMock(\Magento\User\Model\User::class);
        $userMock->method('getUserName')->willReturn('admin');
        $this->authSessionMock->method('getUser')->willReturn($userMock);

        // Create a mock for the serializer
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->serializerMock->method('serialize')->willReturnCallback(function ($data) {
            if ($data['language'] === 'wrong') {
                throw new \Exception('bad string');
            }
            return json_encode($data);
        });

        // Create a mock for the request object
        $requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $requestMock->method('getParam')
            ->with('request')
            ->willReturnOnConsecutiveCalls('en', 'wrong', 'en');

        // Create a mock for the message manager
        $messageManagerMock = $this->createMock(ManagerInterface::class);
        $messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Audit request have been registered.'));
        $messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('an error occurred'));
        $messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($this->isInstanceOf(\Exception::class), __('Something went wrong while saving the audit request.'));

        // Create a mock for the redirect object
        $redirectMock = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $redirectMock->method('setPath')->with('*/*/index')->willReturnSelf();
        $redirectFactoryMock = $this->createMock(\Magento\Framework\Controller\Result\RedirectFactory::class);
        $redirectFactoryMock->method('create')->willReturn($redirectMock);

        // Create a mock for the context object and set the request, message manager and redirect factory mocks
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->method('getRequest')->willReturn($requestMock);
        $this->contextMock->method('getMessageManager')->willReturn($messageManagerMock);
        $this->contextMock->method('getResultRedirectFactory')->willReturn($redirectFactoryMock);

        $this->saveController = new Save(
            $this->contextMock,
            $this->auditRequestFactoryMock,
            $this->auditRequestRepositoryMock,
            $this->authSessionMock,
            $this->serializerMock
        );
    }

    protected function tearDown(): void
    {
        $this->saveController = null;
        $this->contextMock = null;
        $this->auditRequestFactoryMock = null;
        $this->auditRequestRepositoryMock = null;
        $this->authSessionMock = null;
        $this->serializerMock = null;
        $this->messageManagerMock = null;
    }

    public function testExecute()
    {
        $result0 = $this->saveController->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result0);
        $result1 = $this->saveController->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result1);
        $result2 = $this->saveController->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result2);

    }
}
