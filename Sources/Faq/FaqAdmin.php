<?php

declare(strict_types=1);

namespace Faq;

class FaqAdmin
{
    public const SETTINGS_PAGE = 'settings';
    public const PERMISSIONS_PAGE = 'permissions';
    public const ACTIONS = [
        self::SETTINGS_PAGE,
        self::PERMISSIONS_PAGE,
    ];
    public const PERMISSION_VIEW = 'view';
    public const PERMISSION_DELETE = 'delete';
    public const PERMISSION_ADD = 'add';

    public const PERMISSION_EDIT = 'edit';
    public const PERMISSION_SEARCH = 'search';
    public const PERMISSIONS = [
        self::PERMISSION_VIEW,
        self::PERMISSION_DELETE,
        self::PERMISSION_ADD,
        self::PERMISSION_SEARCH,
        self::PERMISSION_EDIT,
    ];
    public const SETTINGS_ENABLE = 'enable';
    public const SETTINGS_PAGINATION = 'num_faqs';
    public const SETTINGS_SHOW_CAT_LIST = 'show_catlist';
    public const SETTINGS_SHOW_LATEST = 'show_latest';
    public const SETTINGS_SORT_METHOD = 'sort_method';
    public const SETTINGS_MENU_POSITION = 'menu_position';
    public const SETTINGS_USE_JS = 'use_js';
    public const SETTINGS_CARE = 'care';
    public const URL = 'action=admin;area=' . Faq::NAME;
    protected ?FaqUtils $utils;
    protected ?FaqRequest $request;

    public function __construct(?FaqUtils $utils = null, ?FaqRequest $request = null)
	{
        $this->utils = $utils ?? new FaqUtils();
        $this->request = $request ?? new FaqRequest();
	}

	public function addArea(&$areas): void
    {
        $this->loadRequiredFiles();

        $areas['config']['areas'][Faq::NAME] = [
            'label' => $this->utils->text('admin_panel'),
            'function' => [$this, 'main'],
            'icon' => 'posts',
            'subsections' => [
                self::SETTINGS_PAGE => [$this->utils->text('admin_settings')],
                self::PERMISSIONS_PAGE => [$this->utils->text('admin_permissions')],
            ],
        ];
    }

    public function main(): void
    {
        global $context;

        $context[$context['admin_menu_name']]['tab_data'] = [
            'title' => $this->utils->text('admin_panel'),
            'description' => $this->utils->text('admin_panel_desc'),
            'tabs' => [
                self::SETTINGS_PAGE => [],
                self::PERMISSIONS_PAGE => []
            ],
        ];

        $action = $this->request->get('sa');
        $action = $action && in_array($action, self::ACTIONS, true) ?
            $action : self::ACTIONS[0];

        $this->setContext($action);
        $this->{$action}($action);
    }

    public function settings(string $action): void
    {
        $configVars = [
            ['check', Faq::NAME . '_enable','subtext' => $this->utils->text('enable_sub')],
            ['int', Faq::NAME .'_num_faqs', 'size' => 3, 'subtext' => $this->utils->text('num_faqs_sub')],
            ['check', Faq::NAME .'_show_catlist', 'subtext' => $this->utils->text('show_catlist_sub')],
            ['int', Faq::NAME .'_show_latest', 'size' => 3, 'subtext' => $this->utils->text('show_latest_sub')],
            ['select', Faq::NAME .'_sort_method',
                [
                    'id' => $this->utils->text('id'),
                    'title' => $this->utils->text('title'),
                    'cat_id' => $this->utils->text('byCat'),
                    'body' => $this->utils->text('body'),
                ],
                'subtext' => $this->utils->text('sort_method_sub')
            ],
            ['select', Faq::NAME .'_menu_position',
                [
                    'home' => $this->utils->smfText('home'),
                    'help' => $this->utils->smfText('help'),
                    'search' => $this->utils->smfText('search'),
                    'login' => $this->utils->smfText('login'),
                    'register' => $this->utils->smfText('register')
                ],
                'subtext' => $this->utils->text('menu_position_sub')
            ],
            ['check', Faq::NAME .'_use_js', 'subtext' => $this->utils->text('use_j_sub')],
            ['check', Faq::NAME .'_care', 'subtext' => $this->utils->text('care_sub')],

        ];

        if ($this->request->isSet('save')) {
            $this->saveConfig($configVars, $action);
        }

        prepareDBSettingContext($configVars);
    }

    public function permissions(string $action): void
    {
        $configVars = [];

        foreach (self::PERMISSIONS as $permission) {
            $configVars[] = [
                'permissions',
                Faq::NAME . '_' . $permission,
                0,
                $this->utils->smfText('permissionname_'. Faq::NAME . '_' . $permission),
            ];
        }

        if ($this->request->isSet('save')) {
            $this->saveConfig($configVars, $action);
        }

        prepareDBSettingContext($configVars);
    }

    protected function saveConfig(array $configVars, string $action): void
    {
        checkSession();
        saveDBSettings($configVars);
        redirectexit(self::URL . ';sa=' . $action);
    }

    protected function setContext(string $action): void
    {
        global $scripturl;

        $this->utils->setContext([
            'sub_action' => $action,
            'page_title' => $this->utils->text('admin_' . $action),
            'post_url' => $scripturl . '?' . self::URL .';sa=' . $action . ';save',
            'sub_template' => 'show_settings',
            'settings_title' =>  $this->utils->text('admin_' . $action),
        ]);
    }

    protected function loadRequiredFiles(): void
    {
        global $sourcedir;

        isAllowedTo('admin_forum');
        loadLanguage(ucfirst(Faq::NAME));

        require_once($sourcedir . '/ManageSettings.php');
        require_once($sourcedir . '/ManageServer.php');
    }

	public function permissionsList(&$permissionGroups, &$permissionList): void
    {
        $templateName = Faq::NAME . '_%s';
        $classic = 'classic';
        $simple = 'simple';

		$permissionGroups['membergroup'][$simple] = [sprintf($templateName, $simple)];
		$permissionGroups['membergroup'][$classic] = [sprintf($templateName, $classic)];

		foreach (self::PERMISSIONS as $permissionName) {
            $permissionList['membergroup'][sprintf($templateName, $permissionName)] = [
                false,
                sprintf($templateName, 'per_' . $classic),
                sprintf($templateName, 'per_' . $simple),
            ];
        }

	}
}