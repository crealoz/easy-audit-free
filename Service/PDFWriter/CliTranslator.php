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
    private array $translationData;
    public function __construct(
        private readonly Localization        $localization,
        private readonly TranslateInterface $translate,
        private readonly State               $appState
    )
    {

    }

    /**
     * Translate a string
     *
     * @param string $string
     * @return string
     */
    public function translate(string $string): string
    {
        if (!$this->isLanguageInitialized) {
            throw new \RuntimeException('Language not initialized');
        }
        return $this->translationData[$string] ?? $string;
    }

    /**
     * Initialize the language
     *
     * @param string $locale
     * @return void
     */
    public function initLanguage(string $locale): void
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

    /**
     * Get the language fallback. It tries to get the language from the available languages, if it is not available it
     * tries to get the language without the region, if it is not available it uses the default language (en_US).
     *
     * @param string $locale
     * @return string
     */
    private function getLanguageFallback(string $locale): string
    {
        $fallbackLocale = 'en_US';
        $availableLanguages = $this->localization->getAvailableLanguages();
        if (in_array($locale, $availableLanguages)) {
            $fallbackLocale = $locale;
        }
        $languageWithoutRegion = substr($locale, 0, 2);
        if (isset($this->languages[$languageWithoutRegion])) {
            $fallbackLocale = $this->languages[$languageWithoutRegion];
        }
        return $fallbackLocale;
    }
}