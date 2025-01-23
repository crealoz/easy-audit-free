<?php

namespace Crealoz\EasyAudit\Service\Classes;

class ArgumentTypeChecker
{
    public function isArgumentModel(string $argumentName): bool
    {
        return strpos($argumentName, 'Model') !== false;
    }

    /**
     * Check if type finishes by Factory or Interface
     *
     * @param $argument
     * @return bool
     */
    public function isArgumentAnInterfaceOrFactory(string $argumentName): bool
    {
        return substr_compare($argumentName, 'Factory', -strlen('Factory')) === 0 || substr_compare($argumentName, 'Interface', -strlen('Interface')) === 0;
    }

    /**
     * Check if argument is situated in the Magento\Framework\Model namespace
     *
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentMagentoModel(string $argumentName): bool
    {
        return strpos($argumentName, 'Magento\Framework\Model') !== false;
    }

    /**
     * Check if argument is a basic PHP type
     *
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentBasicType(string $argumentName): bool
    {
        return in_array($argumentName, ['string', 'int', 'float', 'bool', 'array']);
    }

    /**
     * Check if argument brings a context
     *
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentContext(string $argumentName): bool
    {
        return strpos($argumentName, 'Context') !== false;
    }

    /**
     * Check if argument is in a standard library
     *
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentStdLib(string $argumentName): bool
    {
        return strpos($argumentName, 'Stdlib') !== false;
    }

    /**
     * Check if argument is a serializer
     *
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentSerializer(string $argumentName): bool
    {
        return strpos($argumentName, 'Serializer') !== false;
    }

    /**
     * Registry is managed elsewhere
     * @param $argumentName
     * @return bool
     */
    public function isArgumentRegistry(string $argumentName): bool
    {
        return $argumentName === 'Magento\Framework\Registry';
    }

    /**
     * Session is managed elsewhere it must use a proxy
     * @param $argumentName
     * @return bool
     */
    public function isArgumentSession(string $argumentName): bool
    {
        return strpos($argumentName, 'Session') !== false;
    }

    /**
     * Helper is managed elsewhere
     * @param $argumentName
     * @return bool
     */
    public function isArgumentHelper(string $argumentName): bool
    {
        return strpos($argumentName, 'Helper') !== false;
    }

    /**
     * Filesystem is managed elsewhere
     * @todo: manage filesystem
     * @param $argumentName
     * @return bool
     */
    public function isArgumentFileSystem(string $argumentName): bool
    {
        return strpos($argumentName, 'Magento\Framework\Filesystem') !== false;
    }

    /**
     * Check if argument is a collection
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentCollection(string $argumentName): bool
    {
        return strpos($argumentName, 'Collection') !== false;
    }

    /**
     * Check if argument is a repository
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentRepository(string $argumentName): bool
    {
        return strpos($argumentName, 'Repository') !== false;
    }

    /**
     * Check if argument is supposed to generate something
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentGenerator(string $argumentName): bool
    {
        return strpos($argumentName, 'Generator') !== false;
    }

    /**
     * Check if argument is a resource model
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentResourceModel(string $argumentName): bool
    {
        return strpos($argumentName, 'ResourceModel') !== false;
    }
}