<?php

namespace Crealoz\EasyAudit\Service\PDFWriter;

use Crealoz\EasyAudit\Service\Localization;
use Magento\Framework\App\State;
use Magento\Framework\TranslateInterface;

class CliTranslator
{
    private array $languages = [
        'en' => 'en_US',
        'es' => 'es_ES',
        'fr' => 'fr_FR',
        'de' => 'de_DE',
        'it' => 'it_IT',
        'other' => 'en_US'
    ];
    private bool $isLanguageInitialized = false;
    private string $language;
    private array $translationData;
    public function __construct(
        private readonly Localization        $localization,
        private readonly TranslateInterface $translate,
        private readonly State               $appState
    )
    {

    }

    public function translate(string $string): string
    {
        if (!$this->isLanguageInitialized) {
            throw new \RuntimeException('Language not initialized');
        }
        return $this->translationData[$string] ?? $string;
    }

    public function initLanguage($locale)
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            // do nothing the area code is already set
        }
        $this->translate->setLocale($this->getLanguageFallback($locale));
        $this->translate->loadData();
        $this->translationData = $this->translate->getData();
        $this->isLanguageInitialized = true;
    }


    private function getLanguageFallback(string $language): string
    {
        $this->language = 'en_US';
        $availableLanguages = $this->localization->getAvailableLanguages();
        if (in_array($language, $availableLanguages)) {
            $this->language = $language;
        }
        $languageWithoutRegion = substr($language, 0, 2);
        if (isset($this->languages[$languageWithoutRegion])) {
            $this->language = $this->languages[$languageWithoutRegion];
        }
        return $this->language;
    }
}