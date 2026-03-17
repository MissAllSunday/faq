<?php

namespace Faq;

class FaqParser
{
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
