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

class Faq extends FaqTools
{
	public function __construct()
	{
		parent::__construct();
	}

function actions(&$actions)
{
	$actions['faq'] = array('Faq.php', 'call#');
}

function menu(&$menu_buttons)
{
	global $scripturl, $modSettings, $txt, $context;

	$insert = $this->enable('menuPosition') ? $this->setting('menuPosition') : 'home';
	$counter = 0;

	foreach ($menu_buttons as $area => $dummy)
		if (++$counter && $area == $insert )
			break;

	$menu_buttons = array_merge(
		array_slice($menu_buttons, 0, $counter),
		array('faq' => array(
			'title' => $this->text('title'),
			'href' => $scripturl . '?action=faq',
			'show' => $this->enable('enable') && allowedTo('faq_view') ? true : false,
			'sub_buttons' => array(
				'faq_admin' => array(
					'title' => $this->text('manageFaqs'),
					'href' => $scripturl . '?action=faq;sa=manage',
					'show' => allowedTo('faq_edit'),
					'sub_buttons' => array(
						'faq_add' => array(
							'title' => $this->text('addNew'),
							'href' => $scripturl . '?action=faq;sa=add',
							'show' => allowedTo('faq_add'),
						),
					),
				),
				'faq_category' => array(
					'title' => $this->text('manageCategories'),
					'href' => $scripturl . '?action=faq;sa=manageCat',
					'show' => allowedTo(array('faq_delete', 'faq_add', 'faq_edit')),
					'sub_buttons' => array(),
				),
				'faq_admin_settings' => array(
					'title' => $this->text('titleAdmin'),
					'href' => $scripturl . '?action=admin;area=modsettings;sa=faq',
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
