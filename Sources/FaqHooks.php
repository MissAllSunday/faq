<?php

/**
 * @package FAQ mod
 * @version 2.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (C) 2013
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('No direct access');

function faq_admin_areas($areas)
{
	global $txt;

	loadLanguage('faq');

	$areas['config']['areas']['modsettings']['subsections']['faq'] = array($txt['faq_title']);
}

function faq_actions($actions)
{
	$actions['faq'] = array('Faq.php', 'faq_dispatch');
}

function faq_menu($menu_buttons)
{
		global $scripturl, $modSettings, $txt, $context;

		loadLanguage('faq');

		$insert = !empty($modSettings['faq_menu_position']) ? $modSettings['faq_menu_position'] : 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert )
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('faq' => array(
				'title' => $txt['faq_title'],
				'href' => $scripturl . '?action=faq',
				'show' => empty($modSettings['faq_enable']) ? false : true,
				'sub_buttons' => array(
					'faq_admin' => array(
						'title' => self::$faq->get('manage', 'Text'),
						'href' => $scripturl . '?action='. faq::$name .';sa=manage',
						'show' => allowedTo('faqperedit'),
						'sub_buttons' => array(
							'faq_add' => array(
								'title' => self::$faq->get('add_send', 'Text'),
								'href' => $scripturl . '?action='. faq::$name .';sa=add',
								'show' => allowedTo('faqperedit'),
							),
						),
					),
					'faq_category' => array(
						'title' => self::$faq->get('manage_category', 'Text'),
						'href' => $scripturl . '?action='. faq::$name .';sa=managecat',
						'show' => allowedTo('faqperedit'),
						'sub_buttons' => array(
							'faq_add' => array(
								'title' => self::$faq->get('addcat_send', 'Text'),
								'href' => $scripturl . '?action='. faq::$name .';sa=addcat',
								'show' => allowedTo('faqperedit'),
							),
						),
					),
				),
			)),
			array_slice($menu_buttons, $counter)
		);

	if (isset($context['current_action']) && $context['current_action'] == 'credits')
		$context['copyrights']['mods'][] = faq_care();
}

function faq_modify_modifications($sub_actions)
{
	global $context;

	$sub_actions['faq'] = 'modify_faq_post_settings';
	$context[$context['admin_menu_name']]['tab_data']['tabs']['faq'] = array();
}

function modify_faq_post_settings($return_config = false)
{
	global $context, $scripturl, $txt;
$config_vars = array(
			array(
				'int',
				'faq_num_faqs',
				'size' => 3,
				'subtext' => self::$faq->get('num_faqs_sub', 'Text')
			),
			array(
				'select',
				'faq_sort_method',
				array(
					'id' => self::$faq->get('id', 'Text'),
					'title' => self::$faq->get('title', 'Text'),
					'timestamp' => self::$faq->get('date', 'Text')
				),
				'subtext' => self::$faq->get('sort_method_sub', 'Text')
			),
			array(
				'select',
				'faq_menu_position',
				array(
					'home' => self::$faq->get('menu_home', 'Text'),
					'help' => self::$faq->get('menu_help', 'Text'),
					'search' => self::$faq->get('menu_search', 'Text'),
					'login' => self::$faq->get('menu_login', 'Text'),
					'register' => self::$faq->get('menu_register', 'Text')
				),
				'subtext' => self::$faq->get('menu_position_sub', 'Text')
			),
		);
	$config_vars = array(
		array('desc', 'faq_admin_desc'),
		array('check', 'faq_enable', 'subtext' => $txt['faq_enable_desc']),
		array('int', 'faq_pag_limit', 'subtext' => $txt['faq_pag_limit_desc'], 'size' => 3),
		array(
			'select',
			'faq_menu_position',
			array(
				'home' => $txt['home'],
				'help' => $txt['help'],
				'search' => $txt['search'],
				'login' => $txt['login'],
				'register' => $txt['register']
			),
			'subtext' => $txt['faq_menu_position_desc']
		),
		array('int', 'faq_sidebar_size','subtext' => $txt['faq_sidebar_size_desc']),
		array('check','faq_sidebar_side', 'subtext' => $txt['faq_sidebar_side_desc']),
		array('check', 'faq_search_engines', 'subtext' => $txt['faq_search_engines_desc']),
		array('check', 'faq_use_javascript', 'subtext' => $txt['']),
		array('check', 'faq_show_all', 'subtext' => $txt['faq_show_all_desc'])),
		array('check', 'faq_care', 'subtext' => $txt['faq_care_desc']),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=faq';
	$context['settings_title'] = $txt['faq_title'];

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

function faq_permissions($permissionGroups, $permissionList)
{
	$permissionGroups['membergroup']['simple'] = array('faq_per_simple');
	$permissionGroups['membergroup']['classic'] = array('faq_per_classic');

	$permissionList['membergroup']['faq_viewfaq'] = array(
		false,
		'faq_per_classic',
		'faq_per_simple');

	$permissionList['membergroup']['faq_deletefaq'] = array(
		false,
		'faq_per_classic',
		'faq_per_simple');
	$permissionList['membergroup']['faq_addfaq'] = array(
		false,
		'faq_per_classic',
		'faq_per_simple');
	$permissionList['membergroup']['faq_editfaq'] = array(
		false,
		'faq_per_classic',
		'faq_per_simple');
}

function faq_care()
{
	return '
<div class="smalltext" style="text-align:center;">
	<a href="http://missallsunday.com" target="_blank" title="Free SMF mods">FAQ mod &copy; Suki</a>
</div>';
}