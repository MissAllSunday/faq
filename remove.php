<?php

/**
 * @package FAQ mod
 * @version 2.0
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, Jessica Gonz�lez
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
 * Jessica Gonz�lez.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
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
		remove_integration_function($hook, $function);