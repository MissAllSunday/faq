<?php

namespace Faq;

/**
 * Censors titles and parses BBC in bodies.
 *
 * No constructor arguments on purpose: SMF builds hook classes with `new $class`
 * and no arguments, so this must stay trivially cheap to instantiate.
 */
class FaqParser
{
    public function parse(array $data): array
    {
        global $sourcedir;

        require_once($sourcedir . '/Subs-Post.php');

        $data['title'] = $data['title'] ?? '';
        $data['body'] = $data['body'] ?? '';

        censorText($data['title']);
        preparsecode($data['body'], true);

        $data['body'] = parse_bbc($data['body']);

        return $data;
    }
}
