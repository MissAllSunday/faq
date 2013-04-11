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

function faq_dispatch()
{
	global $txt, $sourcedir, $modSettings, $context;
	static $faqObject;

		/* Safety first, hardcode the actions */
		$subActions = array(
			'add',
			'add2',
			'delete',
			'edit',
			'categories',
			'search',
			'single',
			'success',
			'manage',
		);

		if (empty($faqObject))
		{
			require_once($sourcedir .'/Subs-faq.php');
			$faqObject = new Faq();
		}

		/* Load both language and template files */
		loadLanguage('faq');
		loadtemplate('faq', 'admin');

		/* It is faster to use $var() than use call_user_func_array */
		if (isset($_GET['sa']))
			$func = $faqObject->clean($_GET['sa']);

		$call = 'faq_' .(!empty($func) && in_array($func, array_values($subActions)) ?  $func : 'main');

		// Call the appropiate function
		$call($faqObject);
}

function faq_main($faqObject)
{
	global $context, $scripturl, $txt, $user_info, $modSettings;

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	$context['sub_template'] = 'faq_main';
	$context['canonical_url'] = $scripturl . '?action=faq';
	$context['page_title'] = $txt['faqmod_title_main'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=faq',
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;

	/* Get all */
	$context['faq']['all'] = $faqObject->getAll();
}

function faq_add($faqObject)
{
	global $context, $scripturl, $txt, $sourcedir;

	/* Check permissions */
	$faqObject->permissions('add', true);

	$context['sub_template'] = 'faq_add';
	$context['page_title'] = $txt['faqmod_adding'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=add',
		'name' => $context['page_title'],
	);

	/* Get the cats */
	$context['faq']['cats'] = $faqObject->getCats();

	/* Tell the template we are adding, not editing */
	$context['faq']['edit'] = false;

	/* We need make sure we have this. */
	require_once($sourcedir . '/Subs-Editor.php');

	/* Create it... */
	$editorOptions = array(
		'id' => 'body',
		'value' => '',
		'width' => '90%',
	);

	/* Magic! */
	create_control_richedit($editorOptions);

	/* ... and store the ID again for use in the form */
	$context['post_box_name'] = $editorOptions['id'];

	/* Pass the object to the template, don't know when is gonna be needed */
	$context['faq']['object'] = $faqObject;
}

function faq_add2($faqObject)
{
	global $context, $scripturl, $sourcedir, $txt, $smcFunc;

	checkSession('post', '', true);

	/* Check permissions */
	$faqObject->permissions('add', true);

	/* Want to see your masterpiece before others? */
	if (isset($_REQUEST['preview']))
	{
		/* Set everything up to be displayed. */
		$context['preview_title'] = $faqObject->clean($_REQUEST['title']);
		$context['preview_message'] = parse_bbc($faqObject->clean($_REQUEST['body'], true));
		$context['preview_cat'] = $faqObject->clean($_REQUEST['category_id']);

		/* We Censor for your protection... */
		censorText($context['preview_title']);
		censorText($context['preview_message']);

		/* Set a descriptive title. */
		$context['page_title'] = $txt['preview'] .' - ' . $context['preview_title'];

		/* Build the link tree... */
		$context['linktree'][] = array(
			'url' => $scripturl . '?action='. faq::$name .';sa=add',
			'name' => $context['page_title'],
		);

		/* Get the cats */
		$context['faq']['cats'] = $faqObject->getCats();

		/* We need to make sure we have this. */
		require_once($sourcedir . '/Subs-Editor.php');

		/* Create it... */
		$editorOptions = array(
			'id' => 'body',
			'value' => isset($_REQUEST['body']) ? str_replace(array('  '), array('&nbsp; '), $smcFunc['htmlspecialchars']($_REQUEST['body'])) : '',
			'width' => '90%',
		);

		/* Magic! */
		create_control_richedit($editorOptions);

		/* Are we comming from editing? */
		if (isset($_REQUEST['edit']))
			$_REQUEST['previewEdit'] = $faqObject->clean($_GET['fid']);

		/* ... and store the ID again for use in the form */
		$context['post_box_name'] = $editorOptions['id'];
		$context['sub_template'] = 'faq_add';
	}

	/* Editing */
	elseif (isset($_REQUEST['edit']))
	{
		if (!isset($_GET['fid']) || empty($_GET['fid']))
			redirectexit('action=faq');

		$lid = $faqObject->clean($_GET['fid']);

		if (empty($lid))
			fatal_lang_error('faqmod_no_valid_id', false);

		/* Make sure it does exists... */
		$current = $faqObject->getBy(false, 'faq', 'id', $lid, 1);

		/* Tell the user this entry doesn't exists anymore */
		if (empty($current))
			fatal_lang_error('faqmod_no_valid_id', false);

		/* Let us continue... */
		$editData = array(
			'cat_id' => $faqObject->clean($_REQUEST['category_id']),
			'log' => $faqObject->createLog(),
			'title' => $faqObject->clean($_REQUEST['title']),
			'body' => $faqObject->clean($_REQUEST['body'], true),
			'id' => $lid
		);

		/* Finally, store the data and tell the user */
		$faqObject->edit($editData, 'faq');
		redirectexit('action=faq;sa=success;pin=edit');
	}

	/* Lastly, adding, make sure it gets executed on adding only */
	elseif (!isset($_REQUEST['edit']) || !isset($_REQUEST['preview']))
	{
		/* Create the data, log would be populated later */
		$data = array(
			'cat_id' => $faqObject->clean($_REQUEST['category_id']),
			'log' => $faqObject->createLog(),
			'title' => $faqObject->clean($_REQUEST['title']),
			'body' => $faqObject->clean($_REQUEST['body'], true),
		);

		$faqObject->add($data);
		redirectexit('action=faq;sa=success;pin=add');
	}
}

function faq_edit($faqObject)
{
	global $context, $scripturl, $modSettings, $sourcedir, $txt;

	$faqObject->permissions('edit', true);

	if (!isset($_GET['fid']) || empty($_GET['fid']))
		redirectexit('action=faq');

	else
	{
		/* Pass the object to the template */
		$context['faq']['object'] = $faqObject;

		/* Trickery, don't ask! */
		if (isset($_REQUEST['body']) && !empty($_REQUEST['body_mode']))
		{
			$_REQUEST['body'] = html_to_bbc($_REQUEST['body']);
			$_REQUEST['body'] = un_htmlspecialchars($_REQUEST['body']);
			$_POST['body'] = $_REQUEST['body'];
		}

		$lid = $faqObject->clean($_GET['fid']);

		if (empty($lid))
			fatal_lang_error('faqmod_no_valid_id', false);

		/* Get the FAQ in question, tell the method this is "manage" */
		$temp = $faqObject->getBy('manage', 'faq', 'id', $lid, 1);

		if (empty($temp))
			fatal_lang_error('faqmod_no_valid_id', false);

		$context['faq']['edit'] = $temp[$lid];
		$context['sub_template'] = 'faq_add';
		$context['page_title'] = $txt['faqmod_editing'] .' - '. $context['faq']['edit']['title'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action='. faq::$name .';sa=edit;fid='. $lid,
			'name' => $context['page_title'],
		);

		/* Get the cats */
		$context['faq']['cats'] = $faqObject->getCats();

		require_once($sourcedir .'/Subs-Editor.php');
		/* Needed for the WYSIWYG editor, we all love the WYSIWYG editor... */
		$modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);

		$editorOptions = array(
			'id' => 'body',
			'value' => un_htmlspecialchars(html_to_bbc($context['faq']['edit']['body'])),
			'width' => '90%',
		);

		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];
	}
}

function faq_delete($faqObject)
{
	global $context, $txt;

	$faqObject->permissions('delete', true);

	/* Gotta have an ID to work with */
	if (!isset($_GET['fid']) || empty($_GET['fid']))
		redirectexit('action=faq');

	else
	{
		$lid = (int) $faqObject->clean($_GET['fid']);
		$table = $faqObject->clean($_GET['table']);
		$faqObject->delete($lid, $table);
		redirectexit('action=faq;sa=success;pin=delete');
	}
}

function faq_success($faqObject)
{
	global $context, $scripturl, $smcFunc, $txt;

	/* No direct access please */
	if (!isset($_GET['pin']) || empty($_GET['pin']))
		redirectexit('action=faq');

	$context['faq']['pin'] = $faqObject->clean($_GET['pin']);

	/* Build the link tree.... */
	$context['linktree'][] = array(
		'url' => $scripturl . '?action='. faq::$name .';sa=success',
		'name' => $txt['faqmod_success_message_title'],
	);

	$context['sub_template'] = 'faq_success';
	$context['faq']['message'] = $txt['faqmod_success_message_'. $context['faq']['pin']];

	/* Set a descriptive title. */
	$context['page_title'] = $txt['faqmod_success_title'];

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_single($faqObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['fid']) || empty($_GET['fid']))
		fatal_lang_error('faq_error_no_valid_action', false);

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	/* Get a valid ID */
	$id = $faqObject->clean($_GET['fid']);

	if (empty($id))
		fatal_lang_error('faq_error_no_valid_action', false);

	/* Does the data has been already loaded? */
	if (!empty($context['faq_all'][$id]))
		$context['faq']['single'] = $context['faq_all'][$id];

	/* No? bugger.. well, get it from the DB */
	else
		$context['faq']['single'] = $faqObject->getSingle($id);

	/* Set all we need */
	$context['sub_template'] = 'faq_single';
	$context['canonical_url'] = $scripturl . '?action='. faq::$name .';sa=single;fid=' . $id;
	$context['page_title'] = $context['faq']['single']['title'] .' - '. $context['faq']['single']['artist'];
	$context['linktree'][] = array(
		'url' => $context['canonical_url'],
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_artist($faqObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['fid']) || empty($_GET['fid']))
		fatal_lang_error('faq_error_no_valid_action', false);

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	$lid = $faqObject->clean($_GET['fid']);

	$context['sub_template'] = 'faq_artist';
	$context['canonical_url'] = $scripturl . '?action='. faq::$name .';sa=artist;fid='. $lid;
	$context['page_title'] = $txt['faq_artist_title'] . $lid;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=artist;fid='. $lid,
		'name' => $context['page_title'],
	);

	/* Get the latest faq from DB */
	$context['faq']['artist'] = $faqObject->getBy('artist', $lid , false);

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_list($faqObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	/* Page stuff */
	$context['sub_template'] = 'faq_list';
	$context['page_title'] = $txt['faq_list_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=list',
		'name' => $txt['faq_list_title'],
	);

	/* No letter? then show the main page */
	if (!isset($_GET['lidletter']) || empty($_GET['lidletter']))
		$context['faq']['list'] = $faqObject->getAll();

	/* Show a list of faq starting with X letter */
	elseif (isset($_GET['lidletter']))
	{
		$lidletter = $faqObject->clean($_GET['lidletter']);

		/* Replace the linktree and title with something more specific */
		$context['page_title'] = $txt['faq_list_title_by_letter'] . $lidletter;
		$context['linktree'][] = array(
			'url' => $scripturl. '?action='. faq::$name .';sa=list;lidletter='. $lidletter,
			'name' => $txt['faq_list_title_by_letter'] . $lidletter,
		);

		$context['faq']['list'] = $faqObject->getBy('title', $lidletter .'%');

		if (empty($context['faq']['list']))
			fatal_lang_error('faq_no_faq_with_letter', false);
	}

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_manage($faqObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$faqObject->permissions(array('edit', 'delete'), true);

	/* Page stuff */
	$context['sub_template'] = 'faq_manage';
	$context['page_title'] = $txt['faqmod_manage'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=manage',
		'name' => $context['page_title'],
	);

	/* Get the cats */
	$context['faq']['cats'] = $faqObject->getCats();

	/* Get all FAQs, show pagination if needed */
	$context['faq']['all'] = $faqObject->getAll('manage');

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_search($faqObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	/* We need a value to serch and a column */
	if (!isset($_REQUEST['l_search_value']) || empty($_REQUEST['l_search_value']) || !isset($_REQUEST['l_column']) || empty($_REQUEST['l_column']))
		fatal_lang_error('faq_error_no_valid_action', false);

	$value = urlencode($faqObject->clean($_REQUEST['l_search_value']));
	$column = $faqObject->clean($_REQUEST['l_column']);

	/* Page stuff */
	$context['sub_template'] = 'faq_list';
	$context['page_title'] = $txt['faq_search_title'] . $value;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=search',
		'name' => $txt['faq_list_title_by_letter'] . $value,
	);

	$context['faq']['list'] = $faqObject->getBy($column, '%'. $value .'%');

	if (empty($context['faq']['list']))
		fatal_lang_error('faq_no_faq_with_letter', false);


	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}
