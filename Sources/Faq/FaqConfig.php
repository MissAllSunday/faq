<?php

namespace Faq;

/**
 * Reads this mod's settings out of $modSettings.
 *
 * No constructor arguments on purpose: SMF builds hook classes with `new $class`
 * and no arguments, so this must stay trivially cheap to instantiate.
 */
class FaqConfig
{
    public function setting(string $settingKey = '', $defaultValue = false)
    {
        global $modSettings;

        $fullKey = Faq::NAME . '_' . $settingKey;

        return !empty($modSettings[$fullKey]) ?
            (ctype_digit((string) $modSettings[$fullKey]) ? ((int) $modSettings[$fullKey]) : $modSettings[$fullKey]) :
            $defaultValue;
    }
}
