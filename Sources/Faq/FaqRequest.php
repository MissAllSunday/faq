<?php

namespace Faq;

class FaqRequest
{
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
}