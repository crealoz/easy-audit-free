<?php

namespace Crealoz\EasyAudit\Test\Unit\Service\Parser;

use Crealoz\EasyAudit\Service\Parser\Functions;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class FunctionsTest extends TestCase
{
    private $functions;

    protected function setUp(): void
    {
        $this->functions = new Functions();
    }

    /**
     * @throws ReflectionException
     */
    public function testGetFunctionContent()
    {
        $class = \Crealoz\EasyAudit\Processor\Files\Di\Plugins\AroundChecker::class;
        $filePath = (new \ReflectionClass($class))->getFileName();
        $functionName = 'execute';

        $content = $this->functions->getFunctionContent($class, $filePath, $functionName);

        $this->assertStringContainsString('public function execute($class): void', $content);
    }
}