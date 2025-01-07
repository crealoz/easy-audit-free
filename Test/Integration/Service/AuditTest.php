<?php

namespace Crealoz\EasyAudit\Test\Integration\Service;

use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Model\AuditRequest;
use Crealoz\EasyAudit\Model\Request\FileFactory;
use Crealoz\EasyAudit\Model\AuditRequestFactory;
use Crealoz\EasyAudit\Processor\Type\Logic;
use Crealoz\EasyAudit\Processor\Type\PHPCode;
use Crealoz\EasyAudit\Processor\Type\TypeFactory;
use Crealoz\EasyAudit\Processor\Type\TypeInterface;
use Crealoz\EasyAudit\Processor\Type\Xml;
use Crealoz\EasyAudit\Service\ArrayTools;
use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Crealoz\EasyAudit\Service\Localization;
use Crealoz\EasyAudit\Service\PDFWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\SerializerInterface;

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

    private $logicMock;

    private $phpMock;

    private $xmlMock;

    private $typeMockBuilder;

    private $fileFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdfWriter = $this->createMock(PDFWriter::class);
        $this->typeFactory = $this->createMock(TypeFactory::class);

        $fileGetter = $this->createMock(FileGetterFactory::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->logicMock = $this->getMockBuilder(Logic::class)
            ->setConstructorArgs([$fileGetter, $logger])
            ->onlyMethods(get_class_methods(TypeInterface::class))
            ->getMock();

        $this->phpMock = $this->getMockBuilder(PHPCode::class)
            ->setConstructorArgs([$fileGetter, $logger])
            ->getMock();
        $this->xmlMock = $this->getMockBuilder(Xml::class)
            ->setConstructorArgs([$fileGetter, $logger])
            ->getMock();

        $objManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->typeMockBuilder = $this->getMockBuilder(TypeFactory::class)
                                    ->onlyMethods(['create', 'get'])
                                    ->setConstructorArgs([
                                        $objManager, [
                                            'php' => $this->phpMock,
                                            'xml' => $this->xmlMock,
                                            'logic' => $this->logicMock
                                        ]
                                    ])
                                    ->getMock();

        $this->typeMockBuilder->method('create')->willReturnMap([
            ['logic', $this->logicMock],
            ['php', $this->phpMock],
            ['xml', $this->xmlMock]
        ]);

        $this->typeMockBuilder->method('get')->willReturnMap([
            ['logic', $this->logicMock],
            ['php', $this->phpMock],
            ['xml', $this->xmlMock]
        ]);

        $this->arrayTools = $this->createMock(ArrayTools::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->auditRequestFactory = $this->createPartialMock(AuditRequestFactory::class, ['create']);
        $this->auditRequestRepository = $this->createMock(AuditRequestRepositoryInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->localization = $this->createMock(Localization::class);
        $this->fileFactory = $this->createMock(FileFactory::class);

        $this->auditRequestFactory->method('create')->willReturn($this->createMock(AuditRequest::class));

        $this->localization->method('initializeLanguage')->willReturn('en_US');

        $this->serializer->method('serialize')->willReturn('{language: en_US}');

        $this->fileFactory->method('create')->willReturn($this->createMock(\Crealoz\EasyAudit\Model\Request\File::class));

        $this->audit = new Audit(
            $this->pdfWriter,
            $this->typeMockBuilder,
            $this->arrayTools,
            $this->logger,
            $this->auditRequestFactory,
            $this->auditRequestRepository,
            $this->serializer,
            $this->localization,
            $this->fileFactory,
            $this->processors
        );
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

        $this->assertInstanceOf(TypeInterface::class, $this->logicMock);

        $this->assertInstanceOf(TypeInterface::class, $this->phpMock);

        $this->assertInstanceOf(TypeInterface::class, $this->xmlMock);

        $typeMock = $this->createMock(TypeInterface::class);
        $this->logicMock->expects($this->atLeastOnce())
            ->method('process');

        $this->phpMock->expects($this->atLeastOnce())
            ->method('process');

        $this->xmlMock->expects($this->atLeastOnce())
            ->method('process');

        $this->logicMock->expects($this->atLeastOnce())
            ->method('hasErrors')
            ->willReturn(false);

        $this->phpMock->expects($this->atLeastOnce())
            ->method('hasErrors')
            ->willReturn(false);

        $this->xmlMock->expects($this->atLeastOnce())
            ->method('hasErrors')
            ->willReturn(false);

        $this->typeMockBuilder->expects($this->atLeastOnce())
            ->method('create')
            ->withAnyParameters()
            ->willReturn($typeMock);

        $this->pdfWriter->expects($this->once())
            ->method('createdPDF');

        $result = $this->audit->run();

        $this->assertIsString($result);
    }
}