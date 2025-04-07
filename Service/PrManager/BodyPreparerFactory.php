<?php


namespace Crealoz\EasyAudit\Service\PrManager;

use Crealoz\EasyAudit\Exception\InvalidPrTypeException;
use Magento\Framework\ObjectManagerInterface;

class BodyPreparerFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {
    }

    /**
     * @param $type
     * @return BodyPreparerInterface
     * @throws InvalidPrTypeException
     */
    public function create($type): BodyPreparerInterface
    {
        return match ($type) {
            'aroundToBeforePlugin', 'aroundToAfterPlugin' => $this->objectManager->create(AroundFunctions::class),
            'noProxyUsedInCommands' => $this->objectManager->create(NoProxyUsedInCommands::class),
            'noProxyUsedForHeavyClasses' => $this->objectManager->create(NoProxyForHeavyClasses::class),
            default => throw new InvalidPrTypeException(__('Invalid type %1', $type)),
        };
    }
}
