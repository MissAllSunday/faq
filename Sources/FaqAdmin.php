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

// Require our libs.
require_once ($sourcedir .'/ohara/src/Suki/Ohara.php');
require_once ($sourcedir .'/FaqTools.php');

class FaqAdmin extends FaqTools
{
	// Fool the system!
	public $name = 'Faq';

	public function __construct()
	{
		parent::__construct();
	}

	function addAdminAreas(&$areas)
	{
		$areas['config']['areas']['modsettings']['subsections']['faq'] = array($this->text('admin'));
	}

	function addModifications(&$sub_actions)
	{
		global $context;

		$sub_actions['faq'] = array($this, 'settings');
		$context[$context['admin_menu_name']]['tab_data']['tabs']['faq'] = array();
	}

	function settings(&$return_config = false)
	{
		global $context, $txt;

		$config_vars = array(
			array('desc', $this->name .'_desc'),
			array('check', $this->name .'_enable', 'subtext' => $this->text('enable_sub')),
			array('int', $this->name .'_num_faqs', 'size' => 3, 'subtext' => $this->text('num_faqs_sub')),
			array('check', $this->name .'_show_catlist', 'subtext' => $this->text('show_catlist_sub')),
			array('int', $this->name .'_show_latest', 'size' => 3, 'subtext' => $this->text('show_latest_sub')),
			array( 'select', $this->name .'_sort_method',
				array(
					'id' => $txt['Faq_id'],
					'title' => $txt['Faq_title'],
					'cat_id' => $txt['Faq_byCat'],
					'body' => $txt['Faq_body'],
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
			array('check', $this->name .'_use_js', 'subtext' => $this->text('use_j_sub')),
			array('check', $this->name .'_care', 'subtext' => $this->text('care_sub')),
		);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $this->scriptUrl . '?action=admin;area=modsettings;sa=faq;save';
		$context['settings_title'] = $this->text('admin');

		if (empty($config_vars))
		{
			$context['settings_save_dont_show'] = true;
			$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

			return prepareDBSettingContext($config_vars);
		}

		if ($this->validate('save'))
		{
			checkSession();
			$save_vars = $config_vars;
			saveDBSettings($save_vars);
			redirectexit('action=admin;area=modsettings;sa=faq');
		}

		prepareDBSettingContext($config_vars);
	}

	function addPermissions(&$permissionGroups, &$permissionList)
	{
		$permissionGroups['membergroup']['simple'] = array('faq_per_simple');
		$permissionGroups['membergroup']['classic'] = array('faq_per_classic');

		foreach ($this->_checkPerm as $p)
			$permissionList['membergroup']['faq_'. $p] = array(
				false,
				'faq_per_classic',
				'faq_per_simple'
			);
	}
}