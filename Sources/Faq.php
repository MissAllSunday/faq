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
			'addCat',
			'add2',
			'delete',
			'edit',
			'editCat',
			'categories',
			'search',
			'single',
			'success',
			'manage',
			'manageCat',
		);

		if (empty($faqObject))
		{
			require_once($sourcedir .'/Subs-faq.php');
			$faqObject = new Faq();
		}

		/* Load both language and template files */
		loadLanguage('Faq');
		loadtemplate('faq', 'admin');

		/* DUH! winning! */
		if (!isset($_GET['sa']) && !empty($modSettings['faqmod_care']))
			$context['insert_after_template'] .= '<div class="smalltext" style="text-align:center;">'. faq_care() .'</div>';

		/* Does the user want to use javascript to show/hide the FAQs? */
		if(!empty($modSettings['faqmod_use_javascript']) && $context['current_action'] == 'faq')
			$context['html_headers'] .= '
	<script language="JavaScript"  type="text/javascript">
	<!--
	function toggleDiv(divid){
		if(document.getElementById(divid).style.display == \'none\'){
			document.getElementById(divid).style.display = \'block\';
		}
		else{
			document.getElementById(divid).style.display = \'none\';
		}
	}
	//-->
	</script>';

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
	$faqObject->permissions(isset($_REQUEST['edit']) ? 'edit' : 'add', true);

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

		/* Pass the object to the template */
		$context['faq']['object'] = $faqObject;
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
		$faqObject->edit($editData);
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

	if (!isset($_GET['fid']) || empty($_GET['fid']) || !isset($_GET['table']) || empty($_GET['table']))
		redirectexit('action=faq');

	else
	{
		/* Pass the object to the template */
		$context['faq']['object'] = $faqObject;

		$lid = $faqObject->clean($_GET['fid']);
		$table = $faqObject->clean($_GET['table']);

		/* Get the cats */
		$context['faq']['cats'] = $faqObject->getCats();

		/* Are we editing a category?, a FAQ? */
		switch($table)
		{
			/* Cats are easier to handle... */
			case 'cat':

				/* Set all the usual stuff */
				$context['faq']['cat']['edit'] = $context['faq']['cats'][$lid];
				$context['sub_template'] = 'faq_addCat';
				$context['page_title'] = $txt['faqmod_editing_cat'] .' - '. $context['faq']['cats'][$lid]['name'];
				$context['linktree'][] = array(
					'url' => $scripturl. '?action='. faq::$name .';sa=edit;fid='. $lid,
					'name' => $context['page_title'],
				);

			break;

			/* Handle FAqs */
			case 'faq':

				/* Trickery, don't ask! */
				if (isset($_REQUEST['body']) && !empty($_REQUEST['body_mode']))
				{
					$_REQUEST['body'] = html_to_bbc($_REQUEST['body']);
					$_REQUEST['body'] = un_htmlspecialchars($_REQUEST['body']);
					$_POST['body'] = $_REQUEST['body'];
				}

				if (empty($lid))
					fatal_lang_error('faqmod_no_valid_id', false);

				/* Get the FAQ in question, tell the method this is "manage" */
				$temp = $faqObject->getBy('manage', 'faq', 'id', $lid, 1);

				if (empty($temp))
					fatal_lang_error('faqmod_no_valid_id', false);

				/* Set all the usual stuff */
				$context['faq']['edit'] = $temp[$lid];
				$context['sub_template'] = 'faq_add';
				$context['page_title'] = $txt['faqmod_editing'] .' - '. $context['faq']['edit']['title'];
				$context['linktree'][] = array(
					'url' => $scripturl. '?action='. faq::$name .';sa=edit;fid='. $lid,
					'name' => $context['page_title'],
				);

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

			break;

			/* Show a nice error message to those unwilling to play nice */
			default;
				fatal_lang_error('faqmod_no_valid_id', false);
			break;
		}
	}
}

function faq_addCat($faqObject)
{
	global $context, $txt;

	$faqObject->permissions('add', true);

	/* Gotta have something to work with */
	if (!isset($_POST['title']) || empty($_POST['title']))
		redirectexit('action=faq');

	else
	{
		$title = $faqObject->clean($_POST['title']);
		$faqObject->addCat(array('category_name' => $title));
		redirectexit('action=faq;sa=success;pin=addCat');
	}
}

function faq_editCat($faqObject)
{
	global $context, $txt;

	$faqObject->permissions('edit', true);

	/* Gotta have something to work with */
	if (!isset($_POST['title']) || empty($_POST['title']))
		redirectexit('action=faq');

	else
	{
		$title = $faqObject->clean($_POST['title']);
		$id = $faqObject->clean($_POST['catID']);

		$editData = array(
			'id' => $id,
			'category_name' => $title,
		);

		/* Finally, store the data and tell the user */
		$faqObject->editCat($editData);
		redirectexit('action=faq;sa=success;pin=editCat');
	}
}

function faq_delete($faqObject)
{
	global $context, $txt;

	$faqObject->permissions('delete', true);

	/* Gotta have an ID to work with */
	if (!isset($_GET['fid']) || empty($_GET['fid']) || !isset($_GET['table']))
		redirectexit('action=faq');

	else
	{
		$lid = (int) $faqObject->clean($_GET['fid']);
		$table = $faqObject->clean($_GET['table']);
		$faqObject->delete($lid, $table);
		redirectexit('action=faq;sa=success;pin=deleteCat');
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

	/* Do not waste my time boy */
	if (!isset($context['faq']['message']))
		redirectexit('action=faq');

	/* Set a descriptive title. */
	$context['page_title'] = $txt['faqmod_success_title'];

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

	/* Get all FAQs, show pagination if needed */
	$context['faq']['all'] = $faqObject->getAll('manage');

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_manageCat($faqObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$faqObject->permissions(array('edit', 'delete'), true);

	/* Page stuff */
	$context['sub_template'] = 'faq_manageCat';
	$context['page_title'] = $txt['faqmod_manage_category'] ;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=manage',
		'name' => $context['page_title'],
	);

	/* Get all possible cats */
	$context['faq']['cats']['all'] = $faqObject->getCats();

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_categories($faqObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	if (!isset($_GET['fid']) || empty($_GET['fid']))
		redirectexit('action=faq');

	$lid = $faqObject->clean($_GET['fid']);

	/* Get all FAQs within certain category */
	$context['faq']['all'] = $faqObject->getBy(false, 'faq', 'cat_id', $lid, false);

	/* The usual stuff */
	$context['sub_template'] = 'faq_main';
	$context['canonical_url'] = $scripturl . '?action=faq;sa=categories';
	$context['page_title'] = $txt['faqmod_categories_list'];
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=faq;sa=categories',
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_search($faqObject)
{
	global $context, $txt, $scripturl, $modSettings;

	/* Are you allowed to see this page? */
	$faqObject->permissions(array('view', 'search'), true);

	/* We need a value to serch and a column */
	if (!isset($_REQUEST['l_search_value']) || empty($_REQUEST['l_search_value']) || !isset($_REQUEST['l_column']) || empty($_REQUEST['l_column']))
		fatal_lang_error('faqmod_no_valid_id', false);

	$value = urlencode($faqObject->clean($_REQUEST['l_search_value']));
	$column = $faqObject->clean($_REQUEST['l_column']);

	/* Page stuff */
	$context['sub_template'] = 'faq_list';
	$context['page_title'] = $txt['faqmod_searc_results'] .' - '. $value;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=search',
		'name' => $context['page_title'],
	);

	$context['faq']['all'] = $faqObject->getBy(false, 'faq', $column, '%'. $value .'%', false, true);

	if (empty($context['faq']['all']))
		fatal_lang_error('faqmod_no_search_results', false);

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function faq_single($faqObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['fid']) || empty($_GET['fid']))
		fatal_lang_error('faqmod_no_valid_id', false);

	/* Are you allowed to see this page? */
	$faqObject->permissions('view', true);

	/* Get a valid ID */
	$id = $faqObject->clean($_GET['fid']);

	if (empty($id))
		fatal_lang_error('faqmod_no_valid_id', false);

	/* All the single ladies! */
	$temp = $faqObject->getBy(false, 'faq', 'id', $id, 1, false);

	if (is_array($temp) && !empty($temp[$id]))
		$context['faq']['single'] = $temp[$id];

	else
		fatal_lang_error('faqmod_no_valid_id', false);

	/* Set all we need */
	$context['sub_template'] = 'faq_single';
	$context['canonical_url'] = $scripturl . '?action=faq;sa=single;fid=' . $id;
	$context['page_title'] = $context['faq']['single']['title'];
	$context['linktree'][] = array(
		'url' => $context['canonical_url'],
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}
