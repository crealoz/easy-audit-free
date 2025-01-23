<?php
namespace Crealoz\EasyAudit\Cron;

use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Audit
{
    protected \Crealoz\EasyAudit\Service\Audit $auditService;
    /**
     * @readonly
     */
    private AuditRequestRepositoryInterface $auditRequestRepository;
    /**
     * @readonly
     */
    private SerializerInterface $serializer;
    public function __construct(\Crealoz\EasyAudit\Service\Audit $auditService, AuditRequestRepositoryInterface $auditRequestRepository, SerializerInterface $serializer)
    {
        $this->auditService = $auditService;
        $this->auditRequestRepository = $auditRequestRepository;
        $this->serializer = $serializer;
    }

    /**
     * @throws \Zend_Pdf_Exception
     * @throws FileSystemException
     */
    public function execute()
    {
        $auditsToBeRun = $this->auditRequestRepository->getAuditsToBeRun();
        foreach ($auditsToBeRun as $auditRequest) {
            /** @var AuditRequestInterface $auditRequest */
            $request = $this->serializer->unserialize($auditRequest->getRequest());
            $language = $request['language'];
            $filename = 'audit_' . $auditRequest->getUsername() . '_' . date('Y-m-d_H-i-s');
            // We ensure that the filename is a valid unix filename if not we remove invalid characters
            if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $filename)) {
                $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $filename);
            }
            $this->auditService->run(null, $language, $filename, $auditRequest->getId());
        }
    }
}
