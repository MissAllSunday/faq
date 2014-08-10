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

class Faq extends Suki\Ohara
{
	public function __construct()
	{
		$this->setRegistry();
	}

	public function call()
	{
		global $context, $scripturl;

			/* Safety first, hardcode the actions */
			$subActions = array(
				'add',
				'addCat',
				'save',
				'delete',
				'edit',
				'categories',
				'search',
				'single',
				'manage',
				'manageCat',
				'addCat',
				'editCat',
				'deleteCat',
			);

			/* Load both language and template files */
			loadLanguage($this->name);
			loadtemplate($this->name);

			$context['linktree'][] = array(
				'url' => $scripturl .'?action=faq',
				'name' => $this->text('title'),
			);

			/* Does the user want to use javascript to show/hide the FAQs? */
			if($this->enable('JavaScript']))
				addInlineJavascript('
		function toggleDiv(divid){
			if(document.getElementById(divid).style.display == \'none\') {
				document.getElementById(divid).style.display = \'block\';
			}
			else {
				document.getElementById(divid).style.display = \'none\';
			}
		}', true);

			// Get the subaction.
			if ($this->data('sa'))
				$call = in_array($this->data('sa'), $subActions) ? $this->data('sa') : 'main';

			else
				$call = 'main';

			// Lazy way to tell the template which action has been called.
			$context['faq']['action'] = $call;

			// "Save" doesn't need a template.
			if ($call != 'save')
				$context['sub_template'] = 'faq_'. $call;

			$context['canonical_url'] = $scripturl . '?action=faq' . (!empty($call) && $call != 'main' ? ';sa='. $call : '');
			$context['page_title'] = $this->text('title_'. $call);
			$context['linktree'][] = array(
				'url' => $context['canonical_url'],
				'name' => $context['page_title'],
			);

			// Call the appropriate function.
			$this->$call();
	}

function main()
{
	global $context;

	// Get all of them.
	$context['faq']['all'] = $this->getAll();
}

function add()
{
	global $context, $scripturl, $sourcedir;

	/* Get the cats */
	$context['faq']['cats'] = $this->getCats();

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
}

function save()
{
	global $context, $scripturl, $sourcedir, $txt, $smcFunc;

	checkSession('post', '', true);

	// Permissions here..

	// Previewing?
	if ($this->data('preview'))
	{
		// Set everything up to be displayed.
		$context['preview_title'] = $this->data('title');
		$context['preview_message'] = parse_bbc($this->data('body'), true);
		$context['preview_cat'] = $this->data('category_id');

		/* We Censor for your protection... */
		censorText($context['preview_title']);
		censorText($context['preview_message']);

		/* Set a descriptive title. */
		$context['page_title'] = $txt['preview'] .' - ' . $context['preview_title'];

		/* Get the cats */
		$context['faq']['cats'] = $this->getCats();

		/* We need to make sure we have this. */
		require_once($sourcedir . '/Subs-Editor.php');

		/* Create it... */
		$editorOptions = array(
			'id' => 'body',
			'value' => $this->data('body') ? $this->data('body') : '', // @check.
			'width' => '90%',
		);

		/* Magic! */
		create_control_richedit($editorOptions);

		/* ... and store the ID again for use in the form */
		$context['post_box_name'] = $editorOptions['id'];
		$context['sub_template'] = 'faq_add';
	}

	/* Editing */
	if ($this->data('preview'))
	{
		// Gotta have something to work with.
		if (!$this->data('fid'))
			fatal_lang_error('Faq_noValidId', false);

		/* Make sure it does exists... */
		$current = $faqObject->getFaqByID($this->data('fid'));

		/* Tell the user this entry doesn't exists anymore */
		if (empty($current))
			fatal_lang_error('Faq_noValidId', false);

		/* Let us continue... */
		$editData = array(
			'cat_id' => $this->data('category_id'),
			'log' => $this->createLog(),
			'title' => $this->data('title'),
			'body' => $faqObject->clean($_REQUEST['body'], true),
			'id' => $lid
		);

		// Perform query here.
		// Set session stuff for notifications.
		redirectexit('action=faq;sa=success;pin=edit');
	}

	/* Lastly, adding, make sure it gets executed on adding only */
	elseif (!isset($_REQUEST['edit']) || !isset($_REQUEST['preview']))
	{
		// Create the data, log would be populated later.
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
	$context['faq']['cats']['all'] = $this->getCats();

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
}
