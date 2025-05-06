<?php

declare(strict_types=1);

namespace Faq;

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;

class Faq
{
	public const NAME = 'faq';
    protected FaqUtils $utils;

    public function __construct(?FaqUtils $utils = null)
    {
        $this->utils = $utils ?? new FaqUtils();
    }

    public function menu(array &$menu_buttons): void
    {
        global $scripturl;

        if (!$this->utils->setting(FaqAdmin::SETTINGS_ENABLE)) {
            return;
        }

        $menuReference = $this->utils->setting(
            FaqAdmin::SETTINGS_MENU_POSITION,
            $this->utils->smfText('home'));
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
                'title' => $this->utils->text('title_main'),
                'href' => $this->buildUrl(FaqController::ACTION),
                'show' => allowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW),
                'sub_buttons' => [
                    'faq_category' => [
                        'title' => $this->utils->text('category_index_title'),
                        'href' => $this->buildUrl(CategoryController::ACTION, CategoryController::SUB_ACTION_MANAGE),
                        'show' => allowedTo([
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD,
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE]),
                        'sub_buttons' => [],
                    ],
                    'faq_manage' => [
                        'title' => $this->utils->text('manage_title'),
                        'href' => $this->buildUrl(FaqController::ACTION, FaqController::SUB_ACTION_MANAGE),
                        'show' => allowedTo([
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD,
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE]),
                        'sub_buttons' => [],
                    ],
                    'faq_admin' => [
                        'title' => $this->utils->text('admin_panel'),
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
