<?php

namespace Faq;

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
