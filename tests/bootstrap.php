<?php

declare(strict_types=1);

define('ROOT', __DIR__);
define('SMF', true);

// mock globals used by SMF
global $sourcedir, $scripturl, $modSettings;
global $boarddir, $boardurl, $context, $txt, $smcFunc, $user_info;

// Function DB
$smcFunc['htmltrim'] = function ($value) {
    return trim($value);
};

$smcFunc['htmlspecialchars'] = function ($value) {
    return htmlspecialchars($value, \ENT_QUOTES);
};

// Mock functions
function timeformat($string = ''): string
{
    return $string;
}

function fatal_lang_error(string $errorKey): void
{
    // Do nothing
}

function comma_format(string $number): string
{
    global $txt;
    static $thousands_separator = null, $decimal_separator = null, $decimal_count = null;

    $override_decimal_count = false;

    if ($decimal_separator === null) {
        if (empty($txt['number_format']) ||
            preg_match('~^1([^\d]*)?234([^\d]*)(0*?)$~', $txt['number_format'], $matches) != 1) {
            return $number;
        }

        $thousands_separator = $matches[1];
        $decimal_separator = $matches[2];
        $decimal_count = strlen($matches[3]);
    }

    return number_format((float) $number, 0, $decimal_separator, $thousands_separator);
}
function loadLanguage($template_name): void
{
}
function log_error($string): void
{
}
function add_integration_function(): void
{
}
function remove_integration_function(): void
{
}
function smf_json_decode($s, $array = true)
{
    return json_decode($s, $array);
}

function parse_bbc(string $content): string
{
    return $content;
}

function loadMemberData(array $userIds): array
{
    return in_array(2, $userIds) ? [] : $userIds;
}

function loadMemberContext(int $userId, bool $dummy): array
{
    switch ($userId) {
        case 666:
            $dataToReturn = [
                'link' => '<a href="#">Astaroth</a>',
                'name' => 'Astaroth',
                'avatar' => ['href' => 'avatar_url/astaroth.png'],
            ];

            break;
        case 1:
            $dummy = true;
            $dataToReturn = [
                'link' => 'Guest',
                'name' => 'Guest',
                'avatar' => ['href' => 'avatar_url/default.png'],
            ];

            break;
        default:
            $dataToReturn = [];
    }

    if ($dummy) {
        $dummy = false;
    }

    return $dataToReturn;
}

function allowedTo($permissionName)
{
    $dummyPermissions = [
        'faq_view' => true,
    ];

    return $dummyPermissions[$permissionName] ?? false;
}

function cache_put_data($key, $data, $timeToLive)
{
    return null;
}

$sourcedir = $boarddir = $boardurl = ROOT;
$scripturl = 'localhost';

// Mock some SMF arrays.
$user_info = [
    'id' => 666,
    'is_guest' => false,
];

$context = [
    'session_var' => 'foo',
    'session_id' => 'baz',
];

$modSettings = [
    'CompressedOutput' => false,
    'faq_enable' => true,
    'faq_num_faqs' => 5,
];

$_REQUEST = [
    'xss' => '<script>alert("XSS")</script>',
    'div-image' => '<DIV »
STYLE="background-image: »
url(javascript:alert(\'XSS\')) »
">',
    'foo' => 'baz',
    'url-encoding' => '<A »
HREF="http://%77%77%77%2E%67 »
%6F%6F%67%6C%65%2E%63%6F%6D" »
>XSS</A>',
];


// Composer-Autoloader
require_once $_SERVER['DOCUMENT_ROOT'] . "./Sources/Faq/autoload.php";