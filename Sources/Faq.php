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
	public $name = __CLASS__;
	public $subActions = array(
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

	public function __construct()
	{
		parent::__construct();
	}

	function actions(&$actions)
	{
		$actions['faq'] = array('Faq.php', 'Faq::call#');
	}


	public function call()
	{
		global $context, $scripturl;


			/* Load both language and template files */
			loadLanguage($this->name);
			loadtemplate($this->name);

			// The basic linktree, each subaction needs to add their own.
			$context['linktree'][] = array(
				'url' => $scripturl .'?action=faq',
				'name' => $this->text('title'),
			);

			// Get the subaction.
			$call = $this->data('sa') && in_array($this->data('sa'), $this->subActions) ? $this->data('sa') : 'main';

			// "Save" doesn't need a template.
			if ($call != 'save')
				$context['sub_template'] = 'faq_'. $call;

			$context['canonical_url'] = $scripturl . '?action=faq' . (!empty($call) && $call != 'main' ? ';sa='. $call : '');
			$context['page_title'] = $this->text('title_'. $call);
			$context['linktree'][] = array(
				'url' => $context['canonical_url'],
				'name' => $context['page_title'],
			);

			// Lazy way to tell the template which action has been called.
			$context['faq']['action'] = $call;
			$this->_call = $call;

			// We kinda need a FAQ ID for pretty much everything even if there isn't one!
			$this->_faq = $this->data('faq') ? $this->data('faq') : 0;

			// Does the user want to use javascript to show/hide the FAQs?
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

		// Past some error here...

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

			// We Censor for your protection...
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
			if (!$this->_faq)
				fatal_lang_error('Faq_noValid', false);

			/* Make sure it does exists... */
			$current = $this->getFaqByID($this->_faq);

			/* Tell the user this entry doesn't exists anymore */
			if (empty($current))
				fatal_lang_error('Faq_noValidId', false);

			/* Let us continue... */
			$editData = array(
				'cat_id' => $this->data('category_id'),
				'log' => $this->createLog(),
				'title' => $this->data('title'),
				'body' => $this->data($_REQUEST['body'], true),
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
				'cat_id' => $this->data($_REQUEST['category_id']),
				'log' => $this->createLog(),
				'title' => $this->data($_REQUEST['title']),
				'body' => $this->data($_REQUEST['body'], true),
			);

			$this->create($data);
			redirectexit('action=faq;sa=success;pin=add');
		}
	}

	function delete()
	{
		global $context, $txt;

		$this->permissions('delete', true);

		/* Gotta have an ID to work with */
		if (!isset($_GET['fid']) || empty($_GET['fid']) || !isset($_GET['table']))
			redirectexit('action=faq');

		else
		{
			$lid = (int) $this->data($_GET['fid']);
			$table = $this->data($_GET['table']);
			$this->delete($lid, $table);
			redirectexit('action=faq;sa=success;pin=deleteCat');
		}
	}
}
