<?php

namespace Faq;

/**
 * Resolves language strings for this mod (and plain SMF ones).
 *
 * No constructor arguments on purpose: SMF builds hook classes with `new $class`
 * and no arguments, so this must stay trivially cheap to instantiate.
 */
class FaqTranslator
{
    public function text(string $textKey = ''): string
    {
        global $txt;

        $fullKey = Faq::NAME . '_' . $textKey;

        if (empty($txt[$fullKey])) {
            loadLanguage(ucfirst(Faq::NAME));
        }

        return $txt[$fullKey] ?? '';
    }

    public function smfText(string $textKey = ''): string
    {
        global $txt;

        return $txt[$textKey] ?? '';
    }
}
