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
		'integrate_pre_include' => '$sourcedir/Faq.php',
		'integrate_pre_include' => '$sourcedir/FaqHooks.php',
		'integrate_menu_buttons' => 'faq_menu',
		'integrate_actions' => 'faq_actions',
		'integrate_load_permissions' => 'faq_permissions',
		'integrate_admin_areas' => 'faq_admin_areas',
		'integrate_modify_modifications' => 'faq_modify_modifications',
	);

	foreach ($hooks as $hook => $function)
		add_integration_function($hook, $function);
