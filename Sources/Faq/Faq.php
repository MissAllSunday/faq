<?php

declare(strict_types=1);

namespace Faq;

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;

class Faq
{
	public const NAME = 'faq';
    protected FaqConfig $config;
    protected FaqTranslator $translator;

    public function __construct(
        ?FaqConfig $config = null,
        ?FaqTranslator $translator = null
    ) {
        $this->config = $config ?? new FaqConfig();
        $this->translator = $translator ?? new FaqTranslator();
    }

    public function menu(array &$menu_buttons): void
    {
        global $scripturl;

        if (!$this->config->setting(FaqAdmin::SETTINGS_ENABLE)) {
            return;
        }

        $menuReference = $this->config->setting(
            FaqAdmin::SETTINGS_MENU_POSITION,
            $this->translator->smfText('home'));
        $counter = 0;

        foreach (array_keys($menu_buttons) as $area) {
            $counter++;
            if ($area === $menuReference) {
                break;
            }
        }

        $menu_buttons = array_merge(
            array_slice($menu_buttons, 0, $counter),
            [Faq::NAME => [
                'title' => $this->translator->text('title_main'),
                'href' => $this->buildUrl(FaqController::ACTION),
                'show' => allowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW),
                'sub_buttons' => [
                    'faq_category' => [
                        'title' => $this->translator->text('category_index_title'),
                        'href' => $this->buildUrl(CategoryController::ACTION, CategoryController::SUB_ACTION_MANAGE),
                        'show' => allowedTo([
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD,
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE]),
                        'sub_buttons' => [],
                    ],
                    'faq_manage' => [
                        'title' => $this->translator->text('manage_title'),
                        'href' => $this->buildUrl(FaqController::ACTION, FaqController::SUB_ACTION_MANAGE),
                        'show' => allowedTo([
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD,
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE]),
                        'sub_buttons' => [],
                    ],
                    'faq_admin' => [
                        'title' => $this->translator->text('admin_panel'),
                        'href' => $scripturl . '?action=admin;area=' . Faq::NAME,
                        'show' => allowedTo('admin_forum'),
                    ],
                ],
            ]],
            array_slice($menu_buttons, $counter)
        );
    }

    protected function buildUrl(string $action, ?string $subAction = null): string
    {
        global $scripturl;

        return strtr('{url}?action={action}{subAction}', [
            '{url}' => $scripturl,
            '{action}' => $action,
            '{subAction}' => $subAction ? (';sa=' . $subAction) : '',
        ]);
    }
}
