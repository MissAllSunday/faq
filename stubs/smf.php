<?php

function loadMemberContext(int $userId, bool $display = false): array {}
function loadMemberData(array $userIds): array {}
function getBoardTree(): array {}
function loadtemplate(string $templateName): void {}
function loadLanguage(string $templateName): void {}
function isAllowedTo(array|string $permissionName): void {}
function allowedTo(array|string $permissionName): bool { return true; }
function redirectexit(string $location = ''): void {}
function prepareDBSettingContext(array $configVars): void {}
function saveDBSettings(array $configVars): void {}
function checkSession(string $type = 'post', string $from_action = '', bool $is_fatal = true): string { return ''; }
function un_preparsecode(string $text): string { return $text; }
function createPost(array &$msgOptions, array &$topicOptions, array &$posterOptions): bool { return true; }
function loadCSSFile(string $fileName, array $options = [], string $id = ''): void {}
function loadJavascriptFile(string $fileName, array $options = [], string $id = ''): void {}
function validateToken(string $action, string $type = 'post', bool $reset = true): bool { return true; }
function create_control_richedit(array $editorOptions): void {}
function createToken(string $action, string $type = 'post'): array { return []; }
function censorText(string &$text): void {}
function preparsecode(string &$text, bool $previewing = false): void {}
function parse_bbc(string $content): string { return $content; }
function createList(array $listOptions): void {}
function constructPageIndex(
    string $baseUrl,
    int $start,
    int $maxValue,
    int $numPerPage,
    bool $compact = false
): string {
    return '';
}
function fatal_lang_error(string $errorKey, bool $log = true, array $sprintf = []): void {}