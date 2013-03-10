<?php

/**
 * @package FAQ mod
 * @version 1.2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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

	/* This mod needs php 5.2 or greater, sorry */
	FAQCheck();

	// You can't go fishing without hooks
	$hooks = array(
		'integrate_pre_include' => '$sourcedir/Faq.php',
		'integrate_menu_buttons' => 'FAQ::FaqMenu',
		'integrate_actions' => 'FAQ::FaqAction',
		'integrate_load_permissions' => 'FAQ::FaqPermissions',
		'integrate_admin_areas' => 'FAQ::FaqAdmin',
	);

	$call = 'add_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);

	function FAQCheck()
	{
		if (version_compare(PHP_VERSION, '5.2.0', '<'))
			fatal_error('This mod needs PHP 5.2 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');
	}