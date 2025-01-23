<?php

namespace Crealoz\EasyAudit\Service;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Phrase;
use Magento\Framework\TranslateInterface;
use Psr\Log\LoggerInterface;

class Localization
{
    /**
     * @readonly
     */
    private Filesystem $filesystem;
    /**
     * @readonly
     */
    private Reader $moduleReader;
    /**
     * @readonly
     */
    private \Magento\Framework\TranslateInterface $translator;
    /**
     * @readonly
     */
    private \Magento\Framework\Phrase\Renderer\Translate $translateRenderer;
    /**
     * @readonly
     */
    private State $appState;
    /**
     * @readonly
     */
    protected LoggerInterface $logger;
    public function __construct(Filesystem $filesystem, Reader     $moduleReader, \Magento\Framework\TranslateInterface                 $translator, \Magento\Framework\Phrase\Renderer\Translate        $translateRenderer, State                            $appState, LoggerInterface                 $logger)
    {
        $this->filesystem = $filesystem;
        $this->moduleReader = $moduleReader;
        $this->translator = $translator;
        $this->translateRenderer = $translateRenderer;
        $this->appState = $appState;
        $this->logger = $logger;
    }
    /**
     * Get the available languages for the module
     * @return array
     */
    public function getAvailableLanguages(): array
    {
        $availableModulesLanguages = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_I18N_DIR, 'Crealoz_EasyAudit');
        // Get files in the directory
        $availableModulesLanguages = $this->filesystem->getDirectoryReadByPath($availableModulesLanguages)->read();
        $availableLanguages = [];
        foreach ($availableModulesLanguages as $availableModuleLanguage) {
            $availableLanguages[] = substr($availableModuleLanguage, 0, 5);
        }
        return $availableLanguages;
    }

    /**
     * Initialize the language for the module. It sets the locale and loads the data for the module.
     *
     * @param string|null $locale
     */
    public function initializeLanguage(string $locale = null): string
    {
        try {
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
        } catch (\Exception $e) {
            // do nothing the area code is already set
        }
        $locale = $this->getLanguageFallback($locale);
        $this->translator->setLocale($locale);
        $this->translator->loadData(Area::AREA_GLOBAL, true);
        $placeHolder = Phrase::getRenderer();
        $compositeRenderer = new \Magento\Framework\Phrase\Renderer\Composite([
            $this->translateRenderer,
            $placeHolder,
        ]);
        Phrase::setRenderer($compositeRenderer);
        return $locale;
    }

    /**
     * Get the language fallback. It tries to get the language from the available languages, if it is not available it
     * tries to get the language without the region, if it is not available it uses the default language (en_US).
     *
     * @param string|null $locale
     * @return string
     */
    private function getLanguageFallback(string $locale = null): string
    {
        if ($locale === null) {
            return 'en_US';
        }
        $fallbackLocale = 'en_US';
        $availableLanguages = $this->getAvailableLanguages();
        if (in_array($locale, $availableLanguages)) {
            $fallbackLocale = $locale;
        }
        if ($locale == "zh_HK" || $locale == "zh_TW") {
            $fallbackLocale = "zh_HanT";
        }
        if ($locale == "zh_CN") {
            $fallbackLocale = "zh_HanS";
        }
        $languageWithoutRegion = substr($locale, 0, 2);
        if (isset($this->languages[$languageWithoutRegion])) {
            $fallbackLocale = $this->languages[$languageWithoutRegion];
        }
        return $fallbackLocale;
    }
}