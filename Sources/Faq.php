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
		$call = isset($_GET['sa']) && !empty($_GET['sa']) && isset($subActions[trim(htmlspecialchars($_GET['sa'], ENT_QUOTES))]) ? 'faq_'. $_GET['sa'] : 'faq_main';

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
	$context['faq']['all'] = $faqObject->getAll(empty($modSettings['faq_latest_limit']) ? 10 : $modSettings['faq_latest_limit']);
}

function faq_add($faqObject)
{
	global $context, $scripturl, $txt, $sourcedir;

	/* Check permissions */
	$faqObject->permissions('add', true);

	$context['sub_template'] = 'faq_add';
	$context['page_title'] = $txt['faq_post_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=add',
		'name' => $txt['faq_post_title'],
	);

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;

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

	create_control_richedit($editorOptions);

	/* ... and store the ID again for use in the form */
	$context['post_box_name'] = $editorOptions['id'];
}

function faq_add2($faqObject)
{
	global $context, $scripturl, $user_info, $sourcedir, $txt, $smcFunc;

	checkSession('post', '', true);

	/* Check permissions */
	$faqObject->permissions('add', true);

	/* Want to see your masterpiece before others? */
	if (isset($_REQUEST['preview']))
	{
		/* Set everything up to be displayed. */
		$context['preview_subject'] = $faqObject->clean($_REQUEST['title']);
		$context['preview_artist'] = $faqObject->clean($_REQUEST['artist']);
		$context['preview_message'] = $faqObject->clean($_REQUEST['body'], true);

		/* Parse out the BBC if it is enabled. */
		$context['preview_message'] = parse_bbc($context['preview_message']);

		/* We Censor for your protection... */
		censorText($context['preview_subject']);
		censorText($context['preview_artist']);
		censorText($context['preview_message']);

		/* Build the link tree.... */
		$context['linktree'][] = array(
			'url' => $scripturl . '?action='. faq::$name .';sa=add',
			'name' => $txt['faq_preview_add'],
		);

		/* We need make sure we have this. */
		require_once($sourcedir . '/Subs-Editor.php');

		/* Create it... */
		$editorOptions = array(
			'id' => 'body',
			'value' => isset($_REQUEST['body']) ? str_replace(array('  '), array('&nbsp; '), $smcFunc['htmlspecialchars']($_REQUEST['body'])) : '',
			'width' => '90%',
		);

		create_control_richedit($editorOptions);

		/* ... and store the ID again for use in the form */
		$context['post_box_name'] = $editorOptions['id'];
		$context['sub_template'] = 'faq_add';

		/* Set a descriptive title. */
		$context['page_title'] = $txt['preview'] .' - ' . $context['preview_subject'];
	}

	/* Editing */
	elseif (isset($_REQUEST['edit']))
	{
		if (!isset($_GET['lid']) || empty($_GET['lid']))
			redirectexit('action=faq');

		$lid = (int) $faqObject->clean($_GET['lid']);

		/* Make usre it does exists... */
		$current = $faqObject->getBy('id', $lid, 1);

		/* Tell the user this entry doesn't exists anymore */
		if (empty($current))
			fatal_lang_error('faq_no_valid_id', false);

		/* Let us continue... */
		$editData = array(
			'id' => $lid,
			'artist' => $faqObject->clean($_REQUEST['artist']),
			'title' => $faqObject->clean($_REQUEST['title']),
			'body' => $faqObject->clean($_REQUEST['body'], true),
		);

		/* Finally, store the data and tell the user */
		$faqObject->edit($editData);
		redirectexit('action=faq;sa=success;pin=edit');
	}

	/* Lastly, Adding */
	else
	{
		/* Create the data */
		$data = array(
			'user' => $user_info['id'],
			'artist' => $faqObject->clean($_REQUEST['artist']),
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

	if (!isset($_GET['lid']) || empty($_GET['lid']))
		redirectexit('action=faq');

	else
	{
		/* Pass the object to the template */
		$context['faq']['object'] = $faqObject;

		if (isset($_REQUEST['body']) && !empty($_REQUEST['body_mode']))
		{
			$_REQUEST['body'] = html_to_bbc($_REQUEST['body']);
			$_REQUEST['body'] = un_htmlspecialchars($_REQUEST['body']);
			$_POST['body'] = $_REQUEST['body'];
		}

		$lid = (int) $faqObject->clean($_GET['lid']);

		$temp = $faqObject->getBy('id', $lid, 1);

		if (empty($temp))
			fatal_lang_error('faq_no_valid_id', false);

		$context['faq']['edit'] = $temp[$lid];
		$context['sub_template'] = 'faq_add';
		$context['page_title'] = $txt['faq_preview_edit'] .' - '. $context['faq']['edit']['title'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action='. faq::$name .';sa=edit;fid='. $lid,
			'name' => $txt['faq_preview_edit'] .' - '. $context['faq']['edit']['title'],
		);

		require_once($sourcedir .'/Subs-Editor.php');
		/* Needed for the WYSIWYG editor, we all love the WYSIWYG editor... */
		$modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);

		$editorOptions = array(
			'id' => 'body',
			'value' => html_to_bbc(un_htmlspecialchars($context['faq']['edit']['body'])),
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

	if (!isset($_GET['lid']) || empty($_GET['lid']))
		redirectexit('action=faq');

	else
	{
		$lid = (int) $faqObject->clean($_GET['lid']);
		$faqObject->delete($lid);
		redirectexit('action=faq;sa=success;pin=delete');
	}
}

function faq_success($faqObject)
{
	global $context, $scripturl, $smcFunc, $txt;

	if (!isset($_GET['pin']) || empty($_GET['pin']))
		redirectexit('action=faq');

	$context['faq']['pin'] = trim($smcFunc['htmlspecialchars']($_GET['pin']));

		/* Build the link tree.... */
		$context['linktree'][] = array(
			'url' => $scripturl . '?action='. faq::$name .';sa=success',
			'name' => $txt['faq_success_message_title'],
		);

		$context['sub_template'] = 'faq_success';
		$context['faq']['message'] = $txt['faq_success_message_'. $context['faq']['pin']];

		/* Set a descriptive title. */
		$context['page_title'] = $txt['faq_success_title'];

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_single($faqObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['lid']) || empty($_GET['lid']))
		fatal_lang_error('faq_error_no_valid_action', false);

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	/* Get a valid ID */
	$id = $faqObject->clean($_GET['lid']);

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
	if (!isset($_GET['lid']) || empty($_GET['lid']))
		fatal_lang_error('faq_error_no_valid_action', false);

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	$lid = $faqObject->clean($_GET['lid']);

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
	$context['page_title'] = $txt['faq_manage_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=manage',
		'name' => $context['page_title'],
	);

	/* No letter? then show the main page */
	if (!isset($_GET['lidletter']) || empty($_GET['lidletter']))
		$context['faq']['list'] = $faqObject->getAll('manage');

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
