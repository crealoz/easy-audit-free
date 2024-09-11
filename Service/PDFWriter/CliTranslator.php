<?php

namespace Crealoz\EasyAudit\Service\PDFWriter;

use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;
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
        private readonly Filesystem       $filesystem,
        private readonly Reader           $moduleReader,
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
        $this->appState->setAreaCode('adminhtml');
        $this->translate->setLocale($this->getLanguageFallback($locale));
        $this->translate->loadData();
        $this->translationData = $this->translate->getData();
        $this->isLanguageInitialized = true;
    }


    private function getLanguageFallback(string $language): string
    {
        $this->language = 'en_US';
        $availableModulesLanguages = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_I18N_DIR, 'Crealoz_EasyAudit');
        // Get files in the directory
        $availableModulesLanguages = $this->filesystem->getDirectoryReadByPath($availableModulesLanguages)->read();
        $availableLanguages = [];
        foreach ($availableModulesLanguages as $availableModuleLanguage) {
            $availableLanguages[] = substr($availableModuleLanguage, 0, 5);
        }
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