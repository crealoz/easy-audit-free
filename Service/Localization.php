<?php

namespace Crealoz\EasyAudit\Service;

use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\TranslateInterface;

class Localization
{
    public function __construct(
        private readonly Filesystem       $filesystem,
        private readonly Reader           $moduleReader
    )
    {

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
}