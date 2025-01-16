<?php

namespace Crealoz\EasyAudit\Test\Integration\Service;

use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Model\AuditRequest;
use Crealoz\EasyAudit\Model\Request\File;
use Crealoz\EasyAudit\Processor\Results\ErroneousFiles;
use Crealoz\EasyAudit\Processor\Type\Logic;
use Crealoz\EasyAudit\Processor\Type\PHPCode;
use Crealoz\EasyAudit\Processor\Type\TypeFactory;
use Crealoz\EasyAudit\Processor\Type\TypeInterface;
use Crealoz\EasyAudit\Processor\Type\Xml;
use Crealoz\EasyAudit\Service\Audit;
use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\Localization;
use Crealoz\EasyAudit\Service\PDFWriter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
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


    /**
     * @var array
     */
    protected array $processors = [
        'xml' => [
            'di' => [
                'plugins' => \Crealoz\EasyAudit\Processor\Files\Di\Plugins::class,
            ],
            'layout' => [
                'cacheable' => \Crealoz\EasyAudit\Processor\Files\View\Cacheable::class,
            ],
        ],
        'php' => [
            'php' => [
                'sql' => \Crealoz\EasyAudit\Processor\Files\Code\HardWrittenSQL::class,
                'specificclassinjection' => \Crealoz\EasyAudit\Processor\Files\Code\SpecificClassInjection::class,
                'useofregistry' => \Crealoz\EasyAudit\Processor\Files\Code\UseOfRegistry::class,
            ],
        ],
        'logic' => [
            'blockvm' => [
                'ratio' => \Crealoz\EasyAudit\Processor\Files\Code\BlockViewModelRatio::class,
            ],
            'localunusedmodules' => [
                'configphp' => \Crealoz\EasyAudit\Processor\Files\Logic\UnusedModules::class,
            ],
        ],
    ];

    private $logicMock;

    private $phpMock;

    private $xmlMock;

    private $typeMockBuilder;

    private $fileFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeProcessors();

        $this->pdfWriter = $this->createMock(PDFWriter::class);

        $fileGetterFactory = $this->getMockBuilder(FileGetterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $fileGetter = $this->createMock(\Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface::class);

        $falseFiles = array_fill(0, 15, 'file');
        $fileGetter->method('execute')->willReturn(
            $falseFiles
        );

        $fileGetterFactory->method('create')->willReturn($fileGetter);

        $logger = $this->createMock(LoggerInterface::class);

        $this->logicMock = new Logic($fileGetterFactory, $logger);
        $this->phpMock = new PHPCode($fileGetterFactory, $logger);
        $this->xmlMock = new Xml($fileGetterFactory, $logger);

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

        $filesystem = $this->createMock(Filesystem::class);
        $rootDirectory = $this->createMock(ReadInterface::class);
        $rootDirectory->method('getAbsolutePath')->willReturn('/var/www/html');
        $filesystem->method('getDirectoryRead')->willReturnCallback(function ($directory) use ($rootDirectory) {
            if ($directory === DirectoryList::ROOT) {
                return $rootDirectory;
            }
            return $this->createMock(ReadInterface::class);
        });

        $modulePaths = new ModulePaths($filesystem);

        $erroneousFileProcessor = new ErroneousFiles($modulePaths);

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->auditRequestFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\AuditRequestFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->auditRequestRepository = $this->createMock(AuditRequestRepositoryInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->localization = $this->createMock(Localization::class);
        $this->fileFactory = $this->getMockBuilder('\Crealoz\EasyAudit\Model\Request\FileFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->auditRequestFactory->method('create')->willReturn($this->createMock(AuditRequest::class));

        $this->localization->method('initializeLanguage')->willReturn('en_US');

        $this->serializer->method('serialize')->willReturn('{language: en_US}');

        $this->fileFactory->method('create')->willReturn($this->createMock(File::class));

        $this->audit = new Audit(
            $this->pdfWriter,
            $this->typeMockBuilder,
            $this->logger,
            $this->auditRequestFactory,
            $this->auditRequestRepository,
            $this->serializer,
            $this->localization,
            $this->fileFactory,
            $this->processors,
            [$erroneousFileProcessor]
        );


    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->audit);
        unset($this->pdfWriter);
        unset($this->arrayTools);
        unset($this->logicMock);
        unset($this->phpMock);
        unset($this->xmlMock);
        unset($this->typeMockBuilder);
        unset($this->fileFactory);
        unset($this->logger);
        unset($this->auditRequestFactory);
        unset($this->auditRequestRepository);
        unset($this->serializer);
        unset($this->localization);
        unset($this->processors);
        gc_collect_cycles();
    }

    private function initializeProcessors()
    {
        foreach ($this->processors as $type => $processors) {
            foreach ($processors as $processorGroup => $endProcessors) {
                foreach ($endProcessors as $processorName => $processorClass) {
                    $processorMock = $this->getMockBuilder($processorClass)
                        ->disableOriginalConstructor()
                        ->onlyMethods([
                            'run',
                            'getProcessorName',
                            'getProcessorTag',
                            'getResults',
                            'getAuditSection',
                            'getErroneousFiles',
                            'prepopulateResults',
                            'hasErrors'
                        ])
                        ->getMock();
                    $processorMock->expects($this->atMost(1))
                        ->method('run');
                    $hasErrors = rand(0, 1) === 1;
                    $processorMock->method('hasErrors')->willReturn($hasErrors);
                    if ($hasErrors) {
                        $amount = rand(1, 10);
                        $files = [];
                        for ($i = 0; $i < $amount; $i++) {
                            $filename = 'app/code/Vendor/Module/'.uniqid();
                            $files[$filename] = rand(1, 10);
                        }
                        $processorMock->method('getResults')->willReturn([
                            'hasErrors' => true,
                            'errors' => array_keys($files),
                            'warnings' => [],
                            'suggestions' => []
                        ]);
                        $processorMock->method('getErroneousFiles')->willReturn($files);
                    } else {
                        $processorMock->method('getResults')->willReturn([
                            'hasErrors' => false,
                            'errors' => [],
                            'warnings' => [],
                            'suggestions' => []
                        ]);
                        $processorMock->method('getErroneousFiles')->willReturn([]);
                    }
                    $processorMock->method('getProcessorName')->willReturn($processorName);
                    $processorMock->method('getProcessorTag')->willReturn($processorName);
                    $processorMock->method('getAuditSection')->willReturn($processorGroup);
                    $this->processors[$type][$processorGroup][$processorName] = $this->createMock($processorClass);
                }
            }
        }
    }

    public function testRun()
    {

        $this->assertInstanceOf(TypeInterface::class, $this->logicMock);

        $this->assertInstanceOf(TypeInterface::class, $this->phpMock);

        $this->assertInstanceOf(TypeInterface::class, $this->xmlMock);

        $typeMock = $this->createMock(TypeInterface::class);


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