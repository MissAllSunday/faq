<?php

/**
 * @package FAQ mod
 * @version 2.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license https://www.mozilla.org/MPL/2.0/
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	// Everybody likes hooks, especially the ones who adds an extra file everywhere!!
	$hooks = array(
		'integrate_menu_buttons' => '$sources/FaqAdmin.php|Faq::menu#',
		'integrate_actions' => '$sources/Faq.php|Faq::actions#',
		'integrate_load_permissions' => '$sources/FaqAdmin.php|Faq::permissions#',
		'integrate_admin_areas' => '$sources/FaqAdmin.php|Faq::adminAreas#',
		'integrate_modify_modifications' => '$sources/FaqAdmin.php|Faq::modifications#',
	);

	foreach ($hooks as $hook => $function)
		remove_integration_function($hook, $function);
