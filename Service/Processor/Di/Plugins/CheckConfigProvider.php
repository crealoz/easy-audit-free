<?php

namespace Crealoz\EasyAudit\Service\Processor\Di\Plugins;

use Crealoz\EasyAudit\Exception\Processor\Plugins\ConfigProviderPluginException;
use ReflectionException;

class CheckConfigProvider
{
    /**
     * Checks if the class implements \Magento\Checkout\Model\ConfigProviderInterface
     * @param string $pluggedClass
     *
     * @throws ReflectionException
     * @throws ConfigProviderPluginException
     */
    public function execute(string $pluggedClass, string $pluggingClass)
    {
        $reflection = new \ReflectionClass($pluggedClass);
        $interfaces = $reflection->getInterfaceNames();
        if (in_array(\Magento\Checkout\Model\ConfigProviderInterface::class, $interfaces)) {
            throw new ConfigProviderPluginException(__('The class %1 implements \Magento\Checkout\Model\ConfigProviderInterface. This is not allowed.', $pluggedClass), $pluggingClass);
        }
    }
}