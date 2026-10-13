<?php

namespace Faq;

/**
 * Builds every URL this mod outputs.
 *
 * Static on purpose: URLs are needed in hook classes, controllers, services and
 * templates alike, and the only state involved is SMF's own ($scripturl and the
 * session pair in $context). Nothing to inject, nothing to construct.
 */
final class FaqUrl
{
    /**
     * @param string $action the SMF action, e.g. FaqController::ACTION
     * @param string|null $subAction the `sa` parameter, null to omit it
     * @param int|null $id the `id` parameter, null or 0 to omit it
     * @param bool $withSession append the session pair, required by checkSession('get')
     */
    public static function build(
        string $action,
        ?string $subAction = null,
        ?int $id = null,
        bool $withSession = false
    ): string
    {
        global $scripturl;

        $url = $scripturl . '?action=' . $action;

        if (!empty($subAction)) {
            $url .= ';sa=' . $subAction;
        }

        if (!empty($id)) {
            $url .= ';id=' . $id;
        }

        return $withSession ? $url . self::session() : $url;
    }

    /**
     * The `;session_var=session_id` suffix SMF needs on GET links that change state.
     */
    public static function session(): string
    {
        global $context;

        return ';' . $context['session_var'] . '=' . $context['session_id'];
    }
}
