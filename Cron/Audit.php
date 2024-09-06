<?php
namespace Crealoz\EasyAudit\Cron;

use Crealoz\EasyAudit\Api\AuditRepositoryInterface;
use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Audit
{
    public function __construct(
        protected \Crealoz\EasyAudit\Service\Audit $auditService,
        private readonly AuditRepositoryInterface $auditRepository,
        private readonly AuditRequestRepositoryInterface $auditRequestRepository,
        private readonly \Crealoz\EasyAudit\Model\AuditFactory $auditFactory,
        private readonly SerializerInterface $serializer
    )
    {

    }

    public function execute()
    {
        $auditsToBeRun = $this->auditRequestRepository->getAuditsToBeRun();
        foreach ($auditsToBeRun as $auditRequest) {
            /** @var AuditRequestInterface $auditRequest */
            $audit = $this->auditFactory->create();
            $audit->setUser($auditRequest->getUsername());
            $request = $auditRequest->getRequest();
            if ($request) {
                $processors = $this->serializer->unserialize($request);
                $this->auditService->setProcessors($processors);
            }
            $filename = $this->auditService->run();
            $audit->setFilepath($filename);
            $this->auditRepository->save($audit);
            $auditRequest->setExecutionTime(date('Y-m-d H:i:s'));
            $this->auditRequestRepository->save($auditRequest);
        }
    }
}
