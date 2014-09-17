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
		global $context, $scripturl, $txt;

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

		$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=faq';
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
		$permissionGroups['membergroup']['simple'] = array('faq_per_simple');
		$permissionGroups['membergroup']['classic'] = array('faq_per_classic');

		$permissionList['membergroup']['faq_view'] = array(
			false,
			'faq_per_classic',
			'faq_per_simple');

		$permissionList['membergroup']['faq_delete'] = array(
			false,
			'faq_per_classic',
			'faq_per_simple');
		$permissionList['membergroup']['faq_add'] = array(
			false,
			'faq_per_classic',
			'faq_per_simple');
		$permissionList['membergroup']['faq_edit'] = array(
			false,
			'faq_per_classic',
			'faq_per_simple');
		$permissionList['membergroup']['faq_search'] = array(
			false,
			'faq_per_classic',
			'faq_per_simple');
	}
}