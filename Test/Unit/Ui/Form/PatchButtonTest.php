<?php

namespace Crealoz\EasyAudit\Test\Unit\Ui\Form;

use Codeception\PHPUnit\TestCase;
use Crealoz\EasyAudit\Service\Config\MiddlewareHost;
use Crealoz\EasyAudit\Service\PrManager;
use Crealoz\EasyAudit\Ui\Component\Form\Field\PatchButton;

class PatchButtonTest extends TestCase
{
    private $context;
    private $prManager;
    private $resultRepository;
    private $patchButton;

    private $middlewareHost;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->method('getProcessor')->willReturn($this->createMock(\Magento\Framework\View\Element\UiComponent\Processor::class));
        $this->prManager = $this->createMock(PrManager::class);
        $this->resultRepository = $this->createMock(\Crealoz\EasyAudit\Model\ResultRepository::class);
        $this->middlewareHost = $this->createMock(MiddlewareHost::class);
        $this->patchButton = new PatchButton(
            $this->context,
            $this->prManager,
            $this->resultRepository,
            $this->middlewareHost,
            [],
            [
                'config' => ['visible' => true]
            ]
        );
    }

    public function testPrepare()
    {
        $this->context
            ->method('getRequestParams')
            ->willReturn([
                'result_id' => '1'
            ]);
        $result = $this->createMock(\Crealoz\EasyAudit\Api\Data\ResultInterface::class);
        $result->expects($this->once())->method('getProcessor')->willReturn('AroundToBeforePlugin');
        $this->resultRepository
            ->expects($this->once())
            ->method('getById')
            ->willReturn($result);
        $this->patchButton->prepare();
    }

    public function testGetComponentName()
    {
        $this->assertEquals('patchButton', $this->patchButton->getComponentName());
    }
}