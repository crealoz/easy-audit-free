<?php

namespace Crealoz\EasyAudit\Test\Integration\Service;

use Crealoz\EasyAudit\Service\ArrayTools;
use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\PDFWriter;
use Crealoz\EasyAudit\Service\Type\TypeFactory;
use Crealoz\EasyAudit\Service\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Crealoz\EasyAudit\Service\Audit
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class AuditTest extends TestCase
{
    private Audit $audit;
    private PDFWriter $pdfWriter;
    private TypeFactory $typeFactory;
    private ArrayTools $arrayTools;

    /**
     * @var array
     */
    protected array $typeMapping = [
        'php' => 'Crealoz\EasyAudit\Service\Type\PHPCode',
        'xml' => 'Crealoz\EasyAudit\Service\Type\Xml',
        'logic' => 'Crealoz\EasyAudit\Service\Type\Logic'
    ];

    /**
     * @var array
     */
    protected array $processors = [
        'xml' => [
            'di' => [
                'plugins' => 'Crealoz\EasyAudit\Service\Processor\Di\Plugins',
                'preferences' => 'Crealoz\EasyAudit\Service\Processor\Di\Preferences',
                'commands' => 'Crealoz\EasyAudit\Service\Processor\Di\Commands'
            ],
            'layout' => [
                'cacheable' => 'Crealoz\EasyAudit\Service\Processor\View\Cacheable'
            ]
        ],
        'php' => [
            'helpers' => [
                'general' => 'Crealoz\EasyAudit\Service\Processor\Code\Helpers'
            ],
            'php' => [
                'sql' => 'Crealoz\EasyAudit\Service\Processor\Code\HardWrittenSQL',
                'objectManager' => 'Crealoz\EasyAudit\Service\Processor\Code\UseOfObjectManager'
            ]
        ],
        'logic' => [
            'blockvm' => [
                'ratio' => 'Crealoz\EasyAudit\Service\Processor\Code\BlockViewModelRatio'
            ],
            'localunusedmodules' => [
                'configphp' => 'Crealoz\EasyAudit\Service\Processor\Logic\UnusedModules'
            ],
            'vendorunusedmodules' => [
                'configphp' => 'Crealoz\EasyAudit\Service\Processor\Logic\VendorUnusedModules',
                'activedisabled' => 'Crealoz\EasyAudit\Service\Processor\Logic\VendorDisabledModules'
            ]
        ]
    ];

    protected function setUp(): void
    {
        $this->pdfWriter = $this->createMock(PDFWriter::class);
        $this->typeFactory = $this->createMock(TypeFactory::class);
        $this->arrayTools = $this->createMock(ArrayTools::class);

        $this->audit = new Audit($this->pdfWriter, $this->typeFactory, $this->arrayTools, $this->processors);
    }

    protected function tearDown(): void
    {
        unset($this->audit);
        unset($this->pdfWriter);
        unset($this->typeFactory);
        unset($this->arrayTools);
    }

    public function testRun()
    {
        $typeMock = $this->createMock(TypeInterface::class);
        $typeMock->expects($this->atLeastOnce())
            ->method('process');

        $this->typeFactory->expects($this->atLeastOnce())
            ->method('create')
            ->withAnyParameters()
            ->willReturn($typeMock);

        $this->pdfWriter->expects($this->once())
            ->method('createdPDF');

        $result = $this->audit->run();

        $this->assertIsString($result);
    }
}