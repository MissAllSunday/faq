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

    /**
     * Parses an already stored BBC message (the custom message setting).
     *
     * No censoring and no preparsecode here: unlike FAQ bodies, this value is
     * written by the admin settings form and rendered as it was saved.
     */
    public function parseMessage(string $message): string
    {
        global $sourcedir;

        require_once($sourcedir . '/Subs-Post.php');

        return parse_bbc($message);
    }
}
