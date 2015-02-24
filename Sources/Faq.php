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

// Require our libs.
require_once ($sourcedir .'/Suki/Ohara.php');
require_once ($sourcedir .'/FaqTools.php');

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

		// The mod needs to be enable.
		if (!$this->setting('enable'))
			redirectexit();

		// Load both language and template files.
		loadLanguage($this->name);
		loadtemplate($this->name);

		// Is there any messages? Dunno if there is an error or info message...
		$context['faq']['update'] = $this->getAllUpdates();

		// The basic linktree, each subaction needs to add their own.
		$context['linktree'][] = array(
			'url' => $this->scriptUrl .'?action='. $this->name,
			'name' => $this->text('main'),
		);

		// Get the subaction.
		$call = $this->_call = $this->validate('sa') &&  in_array($this->data('sa'), $this->subActions) ? $this->data('sa') : 'main';

		// Check if the user can actually do whatever the user is trying to!
		if (in_array($this->_call, $this->_checkPerm))
			isAllowedTo('faq_'. $this->_call);

		// Get the right template
		$context['sub_template'] = 'faq_'. $call;

		// Add the subaction specific data.
		$context['canonical_url'] = $this->scriptUrl . '?action='. $this->name . (!empty($call) && $call != 'main' ? ';sa='. $call : '');

		// "main" doesn't need this.
		if (!empty($call) && $call != 'main')
		{
			$context['page_title'] = $this->text('action_'. $call);
			$context['linktree'][] = array(
				'url' => $context['canonical_url'],
				'name' => $context['page_title'],
			);
		}

		// Lazy way to tell the template which action has been called.
		$context['faq']['action'] = $call;

		// We kinda need a FAQ ID for pretty much everything even if there isn't one!
		$this->_faq = $this->validate('faq') ? $this->data('faq') : 0;

		// Does the user want to use JavaScript to show/hide the FAQs?
		if($this->enable('use_js'))
			addInlineJavascript('
	function toggleDiv(divid){
		if(document.getElementById(divid).style.display == \'none\') {
			document.getElementById(divid).style.display = \'block\';
		}
		else {
			document.getElementById(divid).style.display = \'none\';
		}
	}', true);

		// All good!
		$this->$call();
	}

	protected function main()
	{
		global $context;

		// Get all of them.
		$context['faq']['all'] = $this->getAll();
	}

	protected function add()
	{
		global $context;

		// Get the cats.
		$context['faq']['cats'] = $this->getCats();

		// Want to see your masterpiece?
		if ($this->validate('preview'))
			$this->preview();

		// Saving?
		if ($this->validate('save'))
				$this->save();

		// Lastly, create our editor instance.
		require_once($this->sourceDir . '/Subs-Editor.php');

		// Create the editor.
		$editorOptions = array(
			'id' => 'body',
			'value' => !empty($context['current']['body']) ? $context['current']['body'] : '',
			'width' => '90%',
		);

		// Magic!
		create_control_richedit($editorOptions);

		// ... and store the ID again for use in the form.
		$context['post_box_name'] = $editorOptions['id'];
	}

	protected function edit()
	{
		// Editing? Assuming there is a faq id...
		if (!$this->_faq)
			fatal_lang_error($this->name .'_noValidId', false);

		// Make sure it does exists...
		$context['current'] = $this->getSingle($this->_faq);

		// Tell the user this entry doesn't exists anymore...
		if (empty($context['current']))
			fatal_lang_error($this->name .'_noValidId', false);

		// Want to see your masterpiece?
		if ($this->validate('preview'))
			$this->preview();

		// Lastly, create our editor instance.
		require_once($this->sourceDir . '/Subs-Editor.php');

		// Create the editor.
		$editorOptions = array(
			'id' => 'body',
			'value' => !empty($context['current']['body'] ? $context['current']['body'] : ''),
			'width' => '90%',
		);

		// Magic!
		create_control_richedit($editorOptions);

		// ... and store the ID again for use in the form.
		$context['post_box_name'] = $editorOptions['id'];
	}

	protected function preview()
	{
		global $txt, $context;

		checkSession('request');

		// A default var for previewing.
		$context['current'] = array();

		require_once($this->sourceDir.'/Subs-Post.php');

		// Set everything up to be displayed.
		$context['current'] = $this->data('current');
		$context['current']['body'] = $this->data('body');
		$context['preview'] = $context['current'];

		// We Censor for your protection...
		censorText($context['preview']['title']);
		preparsecode($context['current']['body'], true);
		preparsecode($context['preview']['body']);
		$context['preview']['body'] = parse_bbc($context['preview']['body']);
		censorText($context['preview']['body']);

		// Set a descriptive title.
		$context['page_title'] = $txt['preview'] .' - ' . $context['current']['title'];
	}

	protected function save()
	{
		global $context, $txt;

		checkSession('request', $this->name);

		require_once($this->sourceDir.'/Subs-Post.php');

		$data = $this->data('current');
		$body = $this->data('body');
		$isEmpty = array();

		// You need to enter something!
		if (empty($data))
		{
			// Set everything up to be displayed.
			$context['current'] = $this->data('current');
			preparsecode($context['current']['body'], true);

			// Fool the system, again!
			$context['faq']['update']['error'] = str_replace('{fields}', implode(', ', $isEmpty), $this->text('error_emtpyFields'));
			return;
		}

		if (empty($body))
			$isEmpty[] = 'body';

		foreach ($data as $k => $v)
			if (empty($v))
				$isEmpty[] = $k;

		// Did you forgot something?
		if (!empty($isEmpty))
		{
			// Set everything up to be displayed.
			$context['current'] = $this->data('current');
			preparsecode($context['current']['body'], true);

			// Fool the system, again!
			$context['faq']['update']['error'] = $this->text('error_emtpyAll');
			return;
		}

		// Add some more things we need.
		$data = array(
			'log' => $this->createLog(),
			'body' => preparsecode($data['body']),
		);

		// Finally store it.
		$id = $this->create($data);

		if (!empty($id))
		{
			$this->setUpdate('info', $this->text('success_'. $this->_call));
			redirectexit('action='. $this->name);
		}
	}

	protected function delete()
	{
		global $context, $txt;

		if (!$this->_faq)
			fatal_lang_error($this->name .'_noValidId', false);

		// Delete the entry.
		$this->erase($this->_faq);

		// Set some kind of response here.
	}

	protected function single()
	{
		// There's gotta be an ID
		if (!$this->_faq)
			fatal_lang_error($this->name .'_noValidId', false);

		// Make sure it does exists...
		$context['faq']['single'] = $this->getSingle($this->_faq);

		// Tell the user this entry doesn't exists anymore...
		if (empty($context['current']))
			fatal_lang_error($this->name .'_noValidId', false);
	}
}
