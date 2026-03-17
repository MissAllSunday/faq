<?php

namespace Faq;

class FaqConfig
{
    public function setting(string $settingKey = '', $defaultValue = false)
    {
        global $modSettings;

        $fullKey = Faq::NAME . '_' . $settingKey;

        return !empty($modSettings[$fullKey]) ?
            (ctype_digit($modSettings[$fullKey]) ? ((int) $modSettings[$fullKey]) : $modSettings[$fullKey]) :
            $defaultValue;
    }
}
