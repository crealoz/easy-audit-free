<?php
namespace Crealoz\EasyAudit\Test\Unit\Cron;

use Crealoz\EasyAudit\Cron\Audit;
use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Crealoz\EasyAudit\Service\Audit as AuditService;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;

class AuditTest extends TestCase
{
    private $auditService;
    private $auditRequestRepository;
    private $serializer;
    private $audit;

    protected function setUp(): void
    {
        $this->auditService = $this->createMock(AuditService::class);
        $this->auditRequestRepository = $this->createMock(AuditRequestRepositoryInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $this->audit = new Audit(
            $this->auditService,
            $this->auditRequestRepository,
            $this->serializer
        );
    }

    public function testExecute()
    {
        $auditRequest = $this->createMock(AuditRequestInterface::class);
        $auditRequest->method('getRequest')->willReturn('{"language":"en"}');
        $auditRequest->method('getUsername')->willReturn('testuser');
        $auditRequest->method('getId')->willReturn(1);

        $auditRequest1 = $this->createMock(AuditRequestInterface::class);
        $auditRequest1->method('getRequest')->willReturn('{"language":"fr"}');
        $auditRequest1->method('getUsername')->willReturn('badtestuser%1!@');
        $auditRequest1->method('getId')->willReturn(2);

        $auditCollectionMock = $this->createMock(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest\Collection::class);
        $auditCollectionMock->method('getIterator')->willReturn(new \ArrayIterator([$auditRequest, $auditRequest1]));

        $this->auditRequestRepository->method('getAuditsToBeRun')->willReturn($auditCollectionMock);
        $this->serializer->method('unserialize')->willReturnOnConsecutiveCalls(['language' => 'en'], ['language' => 'fr']);

        $this->auditService->expects($this->exactly(2))
            ->method('run')
            ->willReturnOnConsecutiveCalls('audit_testuser_', 'audit_badtestuser1_');

        $this->audit->execute();
    }
}