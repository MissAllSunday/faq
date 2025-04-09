<?php

namespace Faq;

use Faq\Controllers\FaqController;

class FaqRequest
{
    protected const HISTORY_LENGTH = 2;
    public function sanitize($variable)
    {
        global $smcFunc;

        if (is_array($variable)) {
            foreach ($variable as $key => $variableValue) {
                $variable[$key] = $this->sanitize($variableValue);
            }

            return array_filter($variable);
        }

        $var = $smcFunc['htmlspecialchars'](
            $smcFunc['htmltrim']((string) $variable),
            \ENT_QUOTES
        );

        if (ctype_digit($var)) {
            $var = (int) $var;
        }

        return $var;
    }

    public function get(string $key, $default = null)
    {
        return $this->isSet($key) ? $this->sanitize($_REQUEST[$key]) : $default;
    }

    public function all()
    {
        return $this->sanitize($_REQUEST);
    }

    public function isSet(string $key): bool
    {
        return isset($_REQUEST[$key]);
    }

    public function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function history(?string $actionName = null): array
    {
        $key = Faq::NAME . '_history';

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key][] = FaqController::ACTION . '_index';
        }

        if ($actionName ) {
            $toCompare = $actionName . '_'. $this->get('sa', 'index');

            // Need to attach an ID?
            $id = $this->get('id', null);

            if ($id) {
                $toCompare .= '_' . $id;
            }

            if (!isset($_SESSION[$key][1]) || ($_SESSION[$key][1] !== $toCompare)) {
                $_SESSION[$key][]  = $toCompare;
            }
        }

        if (count($_SESSION[$key]) > self::HISTORY_LENGTH) {
            array_shift($_SESSION[$key]);
        }

        return $_SESSION[$key];
    }

}