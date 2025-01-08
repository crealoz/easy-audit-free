<?php

namespace Crealoz\EasyAudit\Test\Unit\Command;

use Crealoz\EasyAudit\Console\RunAuditCommand;
use Crealoz\EasyAudit\Model\AuditStorage;
use Crealoz\EasyAudit\Service\Audit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunAuditCommandTest extends TestCase
{
    private $auditServiceMock;
    private $auditStorageMock;
    private $command;

    protected function setUp(): void
    {
        $this->auditServiceMock = $this->createMock(Audit::class);
        $this->auditStorageMock = $this->createMock(AuditStorage::class);

        $this->command = new RunAuditCommand(
            $this->auditServiceMock,
            $this->auditStorageMock
        );
    }

    public function testExecuteWithDefaultOptions()
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $inputMock->method('getOption')->willReturnMap([
            ['language', 'en_US'],
            ['ignored-modules', ''],
        ]);

        $this->auditServiceMock
            ->expects($this->once())
            ->method('run')
            ->with($outputMock, 'en_US');

        $result = $this->command->run($inputMock, $outputMock);

        $this->assertEquals(RunAuditCommand::SUCCESS, $result);
    }

    public function testExecuteWithIgnoredModules()
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $inputMock->method('getOption')->willReturnMap([
            ['language', 'en_US'],
            ['ignored-modules', 'Module1,Module2'], // Ignored modules
        ]);

        $this->auditStorageMock
            ->expects($this->once())
            ->method('setIgnoredModules')
            ->with(['Module1', 'Module2']);

        $this->auditServiceMock
            ->expects($this->once())
            ->method('run')
            ->with($outputMock, 'en_US');

        $result = $this->command->run($inputMock, $outputMock);

        $this->assertEquals(RunAuditCommand::SUCCESS, $result);
    }
}
