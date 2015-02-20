<?php

/**
 * @package FAQ mod
 * @version 2.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license https://www.mozilla.org/MPL/2.0/
 */

if (!defined('SMF'))
	die('No direct access');

class FaqAdmin extends FaqTools
{
	// Fool the system!
	public $name = 'Faq';

	public function __construct()
	{
		parent::__construct();
	}

	function admin(&$areas)
	{
		$areas['config']['areas']['modsettings']['subsections']['faq'] = array($this->text('title'));
	}

	function modifications(&$sub_actions)
	{
		global $context;

		$sub_actions['faq'] = 'modify_faq_post_settings';
		$context[$context['admin_menu_name']]['tab_data']['tabs']['faq'] = array();
	}

	function settings(&$return_config = false)
	{
		global $context, $this->scriptUrl, $txt;

		$config_vars = array(
			array('desc', 'faqmod_desc'),
			array('check', $this->name .'_enable', 'subtext' => $this->text('enable_sub')),
			array('int', $this->name .'_num_faqs', 'size' => 3, 'subtext' => $this->text('enable_sub')),
			array('check', $this->name .'_show_catlist', 'subtext' => $this->text('show_catlist_sub')),
			array('int', $this->name .'_show_latest', 'size' => 3, 'subtext' => $this->text('show_latest_sub')),
			array( 'select', $this->name .'_sort_method',
				array(
					'id' => $txt['faqmod_id'],
					'title' => $txt['faqmod_title'],
					'cat_id' => $txt['faqmod_byCat'],
					'body' => $txt['faqmod_body'],
				),
				'subtext' => $this->text('sort_method_sub')
			),
			array( 'select', $this->name .'_menu_position',
				array(
					'home' => $txt['home'],
					'help' => $txt['help'],
					'search' => $txt['search'],
					'login' => $txt['login'],
					'register' => $txt['register']
				),
				'subtext' => $this->text('menu_position_sub')
			),
			array('check', $this->name .'_use_javascript', 'subtext' => $this->text('use_javascript_sub')),
			array('check', $this->name .'_care', 'subtext' => $this->text('care_sub')),
		);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $this->scriptUrl . '?action=admin;area=modsettings;save;sa=faq';
		$context['settings_title'] = $this->text('title');

		if (empty($config_vars))
		{
			$context['settings_save_dont_show'] = true;
			$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

			return prepareDBSettingContext($config_vars);
		}

		if (isset($_GET['save']))
		{
			checkSession();
			$save_vars = $config_vars;
			saveDBSettings($save_vars);
			redirectexit('action=admin;area=modsettings;sa=faq');
		}

		prepareDBSettingContext($config_vars);
	}

	function permissions(&$permissionGroups, &$permissionList)
	{
		$perm = array('view', 'delete', 'add', 'edit', 'search');

		$permissionGroups['membergroup']['simple'] = array('faq_per_simple');
		$permissionGroups['membergroup']['classic'] = array('faq_per_classic');

		foreach ($perm as $p)
			$permissionList['membergroup']['faq_'. $p] = array(
				false,
				'faq_per_classic',
				'faq_per_simple'
			);
	}


	function menu(&$menu_buttons)
	{
		global $this->scriptUrl, $modSettings, $txt, $context;

		$insert = $this->enable('menuPosition') ? $this->setting('menuPosition') : 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert)
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('faq' => array(
				'title' => $this->text('title'),
				'href' => $this->scriptUrl . '?action=faq',
				'show' => $this->enable('enable') && allowedTo('faq_view') ? true : false,
				'sub_buttons' => array(
					'faq_admin' => array(
						'title' => $this->text('manageFaqs'),
						'href' => $this->scriptUrl . '?action=faq;sa=manage',
						'show' => allowedTo('faq_edit'),
						'sub_buttons' => array(
							'faq_add' => array(
								'title' => $this->text('addNew'),
								'href' => $this->scriptUrl . '?action=faq;sa=add',
								'show' => allowedTo('faq_add'),
							),
						),
					),
					'faq_category' => array(
						'title' => $this->text('manageCategories'),
						'href' => $this->scriptUrl . '?action=faq;sa=manageCat',
						'show' => allowedTo(array('faq_delete', 'faq_add', 'faq_edit')),
						'sub_buttons' => array(),
					),
					'faq_admin_settings' => array(
						'title' => $this->text('titleAdmin'),
						'href' => $this->scriptUrl . '?action=admin;area=modsettings;sa=faq',
						'show' => allowedTo('admin_forum'),
						'sub_buttons' => array(),
					),
				),
			)),
			array_slice($menu_buttons, $counter)
		);
	}

	function care()
	{
		// Pay no attention to that girl behind the curtain...
		if ($this->enable('care'))
			return '
		<a href="http://missallsunday.com" target="_blank" title="Free SMF mods">FAQ mod &copy; Suki</a>';
	}
}