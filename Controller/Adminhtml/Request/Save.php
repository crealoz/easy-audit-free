<?php

namespace Crealoz\EasyAudit\Controller\Adminhtml\Request;

use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Model\AuditRequest;
use Crealoz\EasyAudit\Model\AuditRequestFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Save extends Action implements HttpPostActionInterface
{

    /**
     * @readonly
     */
    private AuditRequestFactory $auditRequestFactory;
    /**
     * @readonly
     */
    private AuditRequestRepositoryInterface $auditRequestRepository;
    /**
     * @readonly
     */
    private \Magento\Backend\Model\Auth\Session $authSession;
    /**
     * @readonly
     */
    private SerializerInterface $serializer;
    public function __construct(
        Action\Context                                       $context,
        AuditRequestFactory                 $auditRequestFactory,
        AuditRequestRepositoryInterface     $auditRequestRepository,
        \Magento\Backend\Model\Auth\Session $authSession,
        SerializerInterface                 $serializer
    )
    {
        $this->auditRequestFactory = $auditRequestFactory;
        $this->auditRequestRepository = $auditRequestRepository;
        $this->authSession = $authSession;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    public function execute()
    {
        try {

            /** @var AuditRequest $auditRequest */
            $auditRequest = $this->auditRequestFactory->create();
            $request = $this->getRequest()->getParam('request');
            $auditRequest->setRequest($this->serializer->serialize(['language' => $request]));
            $auditRequest
                ->setUsername($this->authSession->getUser()->getUserName());
            $this->auditRequestRepository->save($auditRequest);

            $this->messageManager->addSuccessMessage(__('Audit request have been registered.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the audit request.'));
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
