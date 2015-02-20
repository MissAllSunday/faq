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
		'delete',
		'categories',
		'search',
		'single',
		'manage',
		'manageCat',
		'addCat',
		'deleteCat',
	);

	public function __construct()
	{
		parent::__construct();
	}

	function actions(&$actions)
	{
		$actions['Faq'] = array('Faq.php', 'Faq::call#');
	}

	public function call()
	{
		global $context;

		// Load both language and template files.
		loadLanguage($this->name);
		loadtemplate($this->name);

		// The basic linktree, each subaction needs to add their own.
		$context['linktree'][] = array(
			'url' => $this->scriptUrl .'?action=faq',
			'name' => $this->text('title'),
		);

		// Get the subaction.
		$call = $this->data('sa') && in_array($this->data('sa'), $this->subActions) ? $this->data('sa') : 'main';

		// Get the right template
		$context['sub_template'] = 'faq_'. $call;

		// Add the subaction specific data.
		$context['canonical_url'] = $this->scriptUrl . '?action=faq' . (!empty($call) && $call != 'main' ? ';sa='. $call : '');

		// "main" doesn't need this.
		if (!empty($call) && $call != 'main')
		{
			$context['page_title'] = $this->text('title_'. $call);
			$context['linktree'][] = array(
				'url' => $context['canonical_url'],
				'name' => $context['page_title'],
			);
		}

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

		// Call the appropriate method.
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
		global $context;

		// Get the cats.
		$context['faq']['cats'] = $this->getCats();

		// A default var for previewing.
		$context['current'] = array();

		// Want to see your masterpiece?
		if ($this->data('preview'))
		{
			global $txt;

			checkSession('request', 'faq');

			// Set everything up to be displayed.
			$context['current'] = $this->data('current');

			// We Censor for your protection...
			censorText($context['current']['title']);
			censorText($context['current']['message']);

			// Set a descriptive title.
			$context['page_title'] = $txt['preview'] .' - ' . $context['current']['title'];
		}

		// Editing? Assuming there is a faq id...
		if ($this->_faq)
		{
			// Make sure it does exists...
			$context['current'] = $this->getSingle($this->_faq);

			// Tell the user this entry doesn't exists anymore...
			if (empty($context['current']))
				fatal_lang_error($this->name .'_noValidId', false);
		}

		// Lastly, create our editor instance.
		require_once($this->sourceDir . '/Subs-Editor.php');

		// Create the editor.
		$editorOptions = array(
			'id' => 'message',
			'value' => !empty($context['current']['message'] ? $context['current']['message'] : ''),
			'width' => '90%',
		);

		// Magic!
		create_control_richedit($editorOptions);

		// ... and store the ID again for use in the form.
		$context['post_box_name'] = $editorOptions['id'];
	}

	function save()
	{
		global $context, $txt, $smcFunc;

		checkSession('request', 'faq');

		require_once($this->sourceDir.'/Subs-Post.php');

		// Set everything up to be displayed.
		$current = $this->data('current');

		// Gotta make sure we do have something...
		if(empty($current))
			fatal_lang_error($this->name .'_noValidId', false);

		$data = array(
			'cat_id' => $current['cat_id'],
			'log' => $this->createLog(),
			'title' => $current['title'],
			'body' => preparsecode($current['message']);,
		);

		// Finally store it.
		$id = $this->create($data);

		// Some kind of message here.
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
