<?php

namespace Crealoz\EasyAudit\Ui\Request\Form;

use Crealoz\EasyAudit\Service\Localization;

class Languages implements \Magento\Framework\Data\OptionSourceInterface
{
    public function __construct(
        private readonly Localization $localization
    )
    {
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