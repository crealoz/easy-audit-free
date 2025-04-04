<?php


namespace Crealoz\EasyAudit\Processor\Results;

class ChangeIntroduction implements \Crealoz\EasyAudit\Api\Processor\ResultProcessorInterface
{

    public function processResults(array $results): array
    {
        unset($results['introduction']['overall']['summary']['EasyAudit']);
        // Changes disclaimer
        $results['introduction']['overall']['summary']['disclaimer'] = __('This report was generated by EasyAudit Premium. It is intended to provide an overview of the code quality of your Magento 2 store. It is not a comprehensive audit and should not be used as the sole basis for making decisions about your store. It is recommended that you consult with a Magento 2 developer to address any issues found in this report.');
        return $results;
    }
}
