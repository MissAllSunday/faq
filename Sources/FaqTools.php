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

class FaqTools extends Suki\Ohara
{
	public function __construct()
	{
		$this->setRegistry();
	}


function edit($faqObject)
{
	global $context, $scripturl, $modSettings, $sourcedir, $txt;

	$this->permissions('edit', true);

	if (!isset($_GET['fid']) || empty($_GET['fid']) || !isset($_GET['table']) || empty($_GET['table']))
		redirectexit('action=faq');

	else
	{
		/* Pass the object to the template */
		$context['faq']['object'] = $faqObject;

		$lid = $this->clean($_GET['fid']);
		$table = $this->clean($_GET['table']);

		/* Get the cats */
		$context['faq']['cats'] = $this->getCats();

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
				$temp = $this->getBy('manage', 'faq', 'id', $lid, 1);

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

function addCat($faqObject)
{
	global $context, $txt;

	$this->permissions('add', true);

	/* Gotta have something to work with */
	if (!isset($_POST['title']) || empty($_POST['title']))
		redirectexit('action=faq');

	else
	{
		$title = $this->clean($_POST['title']);
		$this->addCat(array('category_name' => $title));
		redirectexit('action=faq;sa=success;pin=addCat');
	}
}

function editCat($faqObject)
{
	global $context, $txt;

	$this->permissions('edit', true);

	/* Gotta have something to work with */
	if (!isset($_POST['title']) || empty($_POST['title']))
		redirectexit('action=faq');

	else
	{
		$title = $this->clean($_POST['title']);
		$id = $this->clean($_POST['catID']);

		$editData = array(
			'id' => $id,
			'category_name' => $title,
		);

		/* Finally, store the data and tell the user */
		$this->editCat($editData);
		redirectexit('action=faq;sa=success;pin=editCat');
	}
}

function delete($faqObject)
{
	global $context, $txt;

	$this->permissions('delete', true);

	/* Gotta have an ID to work with */
	if (!isset($_GET['fid']) || empty($_GET['fid']) || !isset($_GET['table']))
		redirectexit('action=faq');

	else
	{
		$lid = (int) $this->clean($_GET['fid']);
		$table = $this->clean($_GET['table']);
		$this->delete($lid, $table);
		redirectexit('action=faq;sa=success;pin=deleteCat');
	}
}

function success($faqObject)
{
	global $context, $scripturl, $smcFunc, $txt;

	/* No direct access please */
	if (!isset($_GET['pin']) || empty($_GET['pin']))
		redirectexit('action=faq');

	$context['faq']['pin'] = $this->clean($_GET['pin']);

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

function manage($faqObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$this->permissions(array('edit', 'delete'), true);

	/* Page stuff */
	$context['sub_template'] = 'faq_manage';
	$context['page_title'] = $this->text('manageFaqs');
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=manage',
		'name' => $context['page_title'],
	);

	/* Get all FAQs, show pagination if needed */
	$context['faq']['all'] = $this->getAll('manage');

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function manageCat($faqObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$this->permissions(array('edit', 'delete'), true);

	/* Page stuff */
	$context['sub_template'] = 'faq_manageCat';
	$context['page_title'] = $txt['faqmod_manage_category'] ;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=manage',
		'name' => $context['page_title'],
	);

	/* Get all possible cats */
	$context['faq']['cats']['all'] = $this->getCats();

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function categories($faqObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$this->permissions('view', true);

	if (!isset($_GET['fid']) || empty($_GET['fid']))
		redirectexit('action=faq');

	$lid = $this->clean($_GET['fid']);

	/* Get all FAQs within certain category */
	$context['faq']['all'] = $this->getBy(false, 'faq', 'cat_id', $lid, false);

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

function search($faqObject)
{
	global $context, $txt, $scripturl, $modSettings;

	/* Are you allowed to see this page? */
	$this->permissions(array('view', 'search'), true);

	/* We need a value to serch and a column */
	if (!isset($_REQUEST['l_search_value']) || empty($_REQUEST['l_search_value']) || !isset($_REQUEST['l_column']) || empty($_REQUEST['l_column']))
		fatal_lang_error('faqmod_no_valid_id', false);

	$value = urlencode($this->clean($_REQUEST['l_search_value']));
	$column = $this->clean($_REQUEST['l_column']);

	/* Page stuff */
	$context['sub_template'] = 'faq_list';
	$context['page_title'] = $txt['faqmod_searc_results'] .' - '. $value;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action='. faq::$name .';sa=search',
		'name' => $context['page_title'],
	);

	$context['faq']['all'] = $this->getBy(false, 'faq', $column, '%'. $value .'%', false, true);

	if (empty($context['faq']['all']))
		fatal_lang_error('faqmod_no_search_results', false);

	/* Pass the object to the template */
	$context['faq']['object'] = $faqObject;
}

function single($faqObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['fid']) || empty($_GET['fid']))
		fatal_lang_error('faqmod_no_valid_id', false);

	/* Are you allowed to see this page? */
	$this->permissions('view', true);

	/* Get a valid ID */
	$id = $this->clean($_GET['fid']);

	if (empty($id))
		fatal_lang_error('faqmod_no_valid_id', false);

	/* All the single ladies! */
	$temp = $this->getBy(false, 'faq', 'id', $id, 1, false);

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
}
