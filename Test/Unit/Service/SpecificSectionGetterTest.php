<?php
namespace Crealoz\EasyAudit\Test\Unit\Service;

use Crealoz\EasyAudit\Service\PDFWriter\SpecificSectionGetter;
use Crealoz\EasyAudit\Api\Result\SectionInterface;
use PHPUnit\Framework\TestCase;

class SpecificSectionGetterTest extends TestCase
{
    public function testGetSpecificSectionReturnsSection()
    {
        $sectionName = 'testSection';
        $mockSection = $this->createMock(SectionInterface::class);
        $specificSections = [$sectionName => $mockSection];

        $getter = new SpecificSectionGetter($specificSections);
        $result = $getter->getSpecificSection($sectionName);

        $this->assertInstanceOf(SectionInterface::class, $result);
        $this->assertSame($mockSection, $result);
    }

    public function testGetSpecificSectionThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Section nonExistentSection not found');

        $getter = new SpecificSectionGetter([]);
        $getter->getSpecificSection('nonExistentSection');
    }
}