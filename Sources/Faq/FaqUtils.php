<?php

namespace Faq;

class FaqUtils
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

    public function setting(string $settingKey = '', $defaultValue = false)
    {
        global $modSettings;

        $fullKey = Faq::NAME . '_' . $settingKey;

        return !empty($modSettings[$fullKey]) ?
            (ctype_digit($modSettings[$fullKey]) ? ((int) $modSettings[$fullKey]) : $modSettings[$fullKey]) :
            $defaultValue;
    }

    public function setContext(array $values): void
    {
        global $context;

        foreach ($values as $key => $value) {
            $context[$key] = $value;
        }
    }

    public function parse(array $data): array
    {
        global $sourcedir;

        require_once($sourcedir.'/Subs-Post.php');

        $data['title'] = $data['title'] ?? '';
        $data['body'] = $data['body'] ?? '';

        censorText($data['title']);
        preparsecode($data['body'], true);

        $data['body'] = parse_bbc($data['body']);

        return $data;
    }
}