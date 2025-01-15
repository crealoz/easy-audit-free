<?php

namespace Crealoz\EasyAudit\Service\Classes;

class ArgumentTypeChecker
{
    public function isArgumentModel(string $argumentName): bool
    {
        return str_contains($argumentName, 'Model');
    }

    /**
     * Check if type finishes by Factory or Interface
     *
     * @param $argument
     * @return bool
     */
    public function isArgumentAnInterfaceOrFactory(string $argumentName): bool
    {
        return str_ends_with($argumentName, 'Factory') || str_ends_with($argumentName, 'Interface');
    }

    /**
     * Check if argument is situated in the Magento\Framework\Model namespace
     *
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentMagentoModel(string $argumentName): bool
    {
        return str_contains($argumentName, 'Magento\Framework\Model');
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
        return str_contains($argumentName, 'Context');
    }

    /**
     * Check if argument is in a standard library
     *
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentStdLib(string $argumentName): bool
    {
        return str_contains($argumentName, 'Stdlib');
    }

    /**
     * Check if argument is a serializer
     *
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentSerializer(string $argumentName): bool
    {
        return str_contains($argumentName, 'Serializer');
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
        return str_contains($argumentName, 'Session');
    }

    /**
     * Helper is managed elsewhere
     * @param $argumentName
     * @return bool
     */
    public function isArgumentHelper(string $argumentName): bool
    {
        return str_contains($argumentName, 'Helper');
    }

    /**
     * Filesystem is managed elsewhere
     * @todo: manage filesystem
     * @param $argumentName
     * @return bool
     */
    public function isArgumentFileSystem(string $argumentName): bool
    {
        return str_contains($argumentName, 'Magento\Framework\Filesystem');
    }

    /**
     * Check if argument is a collection
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentCollection(string $argumentName): bool
    {
        return str_contains($argumentName, 'Collection');
    }

    /**
     * Check if argument is a repository
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentRepository(string $argumentName): bool
    {
        return str_contains($argumentName, 'Repository');
    }

    /**
     * Check if argument is supposed to generate something
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentGenerator(string $argumentName): bool
    {
        return str_contains($argumentName, 'Generator');
    }

    /**
     * Check if argument is a resource model
     * @param string $argumentName
     * @return bool
     */
    public function isArgumentResourceModel(string $argumentName): bool
    {
        return str_contains($argumentName, 'ResourceModel');
    }
}