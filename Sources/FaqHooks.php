<?php

/**
 * @package FAQ mod
 * @version 2.0
 * @author Jessica González <suki@missallsunday.com>
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

function faq_admin_areas(&$areas)
{
	global $txt;

	if (!isset($txt['faqmod_title_main']))
		loadLanguage('faq');

	$areas['config']['areas']['modsettings']['subsections']['faq'] = array($txt['faqmod_title_main']);
}

function faq_actions(&$actions)
{
	$actions['faq'] = array('Faq.php', 'faq_dispatch');
}

function faq_menu(&$menu_buttons)
{
	global $scripturl, $modSettings, $txt, $context;

	if (!isset($txt['faqmod_title_main']))
		loadLanguage('faq');

	$insert = !empty($modSettings['faq_menu_position']) ? $modSettings['faq_menu_position'] : 'home';
	$counter = 0;

	foreach ($menu_buttons as $area => $dummy)
		if (++$counter && $area == $insert )
			break;

	$menu_buttons = array_merge(
		array_slice($menu_buttons, 0, $counter),
		array('faq' => array(
			'title' => $txt['faqmod_title_main'],
			'href' => $scripturl . '?action=faq',
			'show' => empty($modSettings['faqmod_settings_enable']) ? false : true,
			'sub_buttons' => array(
				'faq_admin' => array(
					'title' => $txt['faqmod_manage'],
					'href' => $scripturl . '?action=faq;sa=manage',
					'show' => allowedTo('faqperedit'),
					'sub_buttons' => array(
						'faq_add' => array(
							'title' => $txt['faqmod_add_send'],
							'href' => $scripturl . '?action=faq;sa=add',
							'show' => allowedTo('faqperedit'),
						),
					),
				),
				'faq_category' => array(
					'title' => $txt['faqmod_manage_category'],
					'href' => $scripturl . '?action=faq;sa=managecat',
					'show' => allowedTo('faqperedit'),
					'sub_buttons' => array(
						'faq_add' => array(
							'title' => $txt['faqmod_addcat_send'],
							'href' => $scripturl . '?action=faq;sa=addcat',
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

function faq_modify_modifications(&$sub_actions)
{
	global $context;

	$sub_actions['faq'] = 'modify_faq_post_settings';
	$context[$context['admin_menu_name']]['tab_data']['tabs']['faq'] = array();
}

function modify_faq_post_settings(&$return_config = false)
{
	global $context, $scripturl, $txt;

	$config_vars = array(
		array('desc', 'faqmod_desc'),
		array('check', 'faqmod_settings_enable', 'subtext' => $txt['faqmod_settings_enable_sub']),
		array('check', 'faqmod_settings_search', 'subtext' => $txt['faqmod_settings_search_sub']),
		array('check', 'faqmod_settings_letterlist', 'subtext' => $txt['faqmod_settings_letterlist_sub']),
		array('int', 'faqmod_num_faqs', 'size' => 3, 'subtext' => $txt['faqmod_num_faqs_sub'] ),
		array( 'select', 'faqmod_sort_method',
			array(
				'id' => $txt['faqmod_id'],
				'title' => $txt['faqmod_title_main'],
				'timestamp' => $txt['faqmod_date']
			),
			'subtext' => $txt['faqmod_sort_method_sub']
		),
		array( 'select', 'faqmod_menu_position',
			array(
				'home' => $txt['home'],
				'help' => $txt['help'],
				'search' => $txt['search'],
				'login' => $txt['login'],
				'register' => $txt['register']
			),
			'subtext' => $txt['faqmod_menu_position_sub']
		),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=faq';
	$context['settings_title'] = $txt['faqmod_title_main'];

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

function faq_permissions(&$permissionGroups, &$permissionList)
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
