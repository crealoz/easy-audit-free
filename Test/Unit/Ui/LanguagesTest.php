<?php

namespace Crealoz\EasyAudit\Test\Unit\Ui;

use Crealoz\EasyAudit\Service\Localization;
use Crealoz\EasyAudit\Ui\Request\Form\Languages;
use PHPUnit\Framework\TestCase;

class LanguagesTest extends TestCase
{
    private $localizationMock;
    private $languages;

    protected function setUp(): void
    {
        $this->localizationMock = $this->createMock(Localization::class);
        $this->languages = new Languages($this->localizationMock);
    }

    public function testToOptionArray()
    {
        $availableLanguages = ['en_US', 'fr_FR', 'de_DE'];

        $this->localizationMock->method('getAvailableLanguages')
            ->willReturn($availableLanguages);

        $expectedOptions = [
            ['value' => 'en_US', 'label' => 'en_US'],
            ['value' => 'fr_FR', 'label' => 'fr_FR'],
            ['value' => 'de_DE', 'label' => 'de_DE']
        ];

        $result = $this->languages->toOptionArray();

        $this->assertEquals($expectedOptions, $result);
    }
}