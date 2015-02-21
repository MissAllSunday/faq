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

	// Everybody likes hooks!
	$hooks = array(
		'integrate_actions' => '$sourcedir/Faq.php|Faq::actions#',
		'integrate_menu_buttons' => '$sourcedir/FaqAdmin.php|FaqAdmin::menu#',
		'integrate_load_permissions' => '$sourcedir/FaqAdmin.php|FaqAdmin::permissions#',
		'integrate_admin_areas' => '$sourcedir/FaqAdmin.php|FaqAdmin::adminAreas#',
		'integrate_modify_modifications' => '$sourcedir/FaqAdmin.php|FaqAdmin::modifications#',
	);

	foreach ($hooks as $hook => $function)
		add_integration_function($hook, $function);
