<?php
namespace Crealoz\EasyAudit\Test\Unit\Ui;

use Crealoz\EasyAudit\Test\Mock\NoConstructorClass;
use Crealoz\EasyAudit\Ui\Request\Form\AuditTypes;
use Crealoz\EasyAudit\Service\Audit;
use PHPUnit\Framework\TestCase;

class AuditTypesTest extends TestCase
{
    private $auditServiceMock;
    private $auditTypes;

    protected function setUp(): void
    {
        $this->auditServiceMock = $this->createMock(Audit::class);

        $this->auditTypes = new AuditTypes(
            $this->auditServiceMock
        );
    }

    public function testToOptionArray()
    {
        $processors = [
            'Processor1' => new NoConstructorClass(),
            'Processor2' => [
                'SubProcessor1' => new NoConstructorClass()
            ]
        ];

        $this->auditServiceMock->method('getAvailableProcessors')
            ->willReturn($processors);

        $expectedOptions = [
            ['label' => 'Processor1', 'value' => 'Processor1:Crealoz\EasyAudit\Test\Mock\NoConstructorClass'],
            ['label' => 'SubProcessor1', 'value' => 'Processor2/SubProcessor1:Crealoz\EasyAudit\Test\Mock\NoConstructorClass']
        ];

        $result = $this->auditTypes->toOptionArray();

        $this->assertEquals($expectedOptions, $result);
    }
}