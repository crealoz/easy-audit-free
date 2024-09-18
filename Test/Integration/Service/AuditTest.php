<?php

namespace Crealoz\EasyAudit\Test\Integration\Service;

use Crealoz\EasyAudit\Processor\Type\TypeFactory;
use Crealoz\EasyAudit\Processor\Type\TypeInterface;
use Crealoz\EasyAudit\Service\ArrayTools;
use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\PDFWriter;
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
        'php' => 'Crealoz\EasyAudit\Processor\Type\PHPCode',
        'xml' => 'Crealoz\EasyAudit\Processor\Type\Xml',
        'logic' => 'Crealoz\EasyAudit\Processor\Type\Logic'
    ];

    /**
     * @var array
     */
    protected array $processors = [
        'xml' => [
            'di' => [
                'plugins' => 'Crealoz\EasyAudit\Processor\Files\Di\Plugins',
                'preferences' => 'Crealoz\EasyAudit\Service\Processor\Di\Preferences',
                'commands' => 'Crealoz\EasyAudit\Service\Processor\Di\Commands'
            ],
            'layout' => [
                'cacheable' => 'Crealoz\EasyAudit\Processor\Files\View\Cacheable'
            ]
        ],
        'php' => [
            'helpers' => [
                'general' => 'Crealoz\EasyAudit\Service\Processor\Code\Helpers'
            ],
            'php' => [
                'sql' => 'Crealoz\EasyAudit\Processor\Files\Code\HardWrittenSQL',
                'objectManager' => 'Crealoz\EasyAudit\Service\Processor\Code\UseOfObjectManager'
            ]
        ],
        'logic' => [
            'blockvm' => [
                'ratio' => 'Crealoz\EasyAudit\Processor\Files\Code\BlockViewModelRatio'
            ],
            'localunusedmodules' => [
                'configphp' => 'Crealoz\EasyAudit\Processor\Files\Logic\UnusedModules'
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