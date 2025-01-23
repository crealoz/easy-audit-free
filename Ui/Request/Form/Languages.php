<?php

namespace Crealoz\EasyAudit\Ui\Request\Form;

use Crealoz\EasyAudit\Service\Localization;

class Languages implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @readonly
     */
    private Localization $localization;
    public function __construct(Localization $localization)
    {
        $this->localization = $localization;
    }

    public function toOptionArray(): array
    {
        $availableLanguages = $this->localization->getAvailableLanguages();
        $languages = [];
        foreach ($availableLanguages as $language) {
            $languages[] = [
                'value' => $language,
                'label' => $language
            ];
        }
        return $languages;
    }
}