<?php

namespace Crealoz\EasyAudit\Test\Unit\Ui\Form;

use Crealoz\EasyAudit\Service\SeverityManager;
use Crealoz\EasyAudit\Ui\Component\Form\Field\Severity;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class SeverityTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->severityMock = $this->createMock(SeverityManager::class);
        $this->severityField = new Severity(
            $this->contextMock,
            $this->uiComponentFactoryMock,
            $this->severityMock
        );
    }

    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'general' =>
                    [
                        'severity_id' => 2
                    ]
            ]
        ];
        $this->severityMock->expects($this->once())
            ->method('getSeverity')
            ->willReturn(['level' => 'critical']);
        $result = $this->severityField->prepareDataSource($dataSource);
        $this->assertEquals('critical', $result['data']['general']['severity']);
    }

}