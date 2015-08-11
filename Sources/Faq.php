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

	// Define the hooks we are going to use.
	protected $_availableHooks = array(
		'actions' => 'integrate_actions',
	);

	public function __construct()
	{
		parent::__construct();
	}

	function addActions(&$actions)
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

		// Let us declare a fancy and quite useful multi-purpose context array.
		$context[$this->name] = array();

		// Is there any messages? Dunno if there is an error or info message...
		$context[$this->name]['update'] = $this->getAllUpdates();
		$context['page_title'] = $this->text('main');

		// The basic linktree, each subaction needs to add their own.
		$context['linktree'][] = array(
			'url' => $this->scriptUrl .'?action='. $this->name,
			'name' => $context['page_title'],
		);

		// Get the subaction.
		$call = $this->_call = $this->validate('sa') &&  in_array($this->data('sa'), $this->subActions) ? $this->data('sa') : 'main';

		// Check if the user can actually do whatever the user is trying to!
		if (in_array($this->_call, $this->_checkPerm))
			isAllowedTo('faq_'. $this->_call);

		// Get the right template.
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
		$context[$this->name]['action'] = $call;

		// Get the cats.
		$context[$this->name]['cats'] = $this->getCats();

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
		$this->{$call}();
	}

	protected function main()
	{
		global $context;

		// Get all of them.
		$context[$this->name]['all'] = $this->getAll();
	}

	protected function add()
	{
		global $context;

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
			return fatal_lang_error($this->name .'_noValidId', false);

		// Make sure it does exists...
		$context['current'] = $this->getSingle($this->_faq);

		// Tell the user this entry doesn't exists anymore...
		if (empty($context['current']))
			fatal_lang_error($this->name .'_noValidId', false);

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

		checkSession('request');

		require_once($this->sourceDir.'/Subs-Post.php');

		$context['current'] = $this->data('current');
		$context['current']['body'] = $this->data('body');
		$isEmpty = array();

		// You need to enter something!
		if (empty($context['current']))
		{
			// Set everything up to be displayed.
			$context['current'] = $this->data('current');
			preparsecode($context['current']['body'], true);

			// Fool the system, again!
			$context[$this->name]['update']['error'] = $this->text('error_emtpyAll');
			return;
		}

		if (empty($context['current']['body']))
			$isEmpty[] = 'body';

		foreach ($context['current'] as $k => $v)
			if (empty($v))
				$isEmpty[] = $k;

		// Did you forgot something?
		if (!empty($isEmpty))
		{
			preparsecode($context['current']['body'], true);

			// Fool the system, again!
			$context[$this->name]['update']['error'] = str_replace('{fields}', implode(', ', $isEmpty), $this->text('error_emtpyFields'));
			return;
		}

		// Add some more things we need.
		$context['current']['log'] = $this->createLog();
		preparsecode($context['current']['body']);

		// Finally store it.
		$id = $this->create($context['current']);

		if (!empty($id))
		{
			$this->setUpdate('info', $this->text('success_'. $this->_call));
			redirectexit('action='. $this->name .';sa='. $this->_call);
		}
	}

	protected function delete()
	{
		global $context, $txt;

		if (!$this->_faq)
			fatal_lang_error($this->name .'_noValidId', false);

		// Delete the entry.
		$this->erase($this->_faq);

		// Redirect them to whatever the page they were viewing.
		$this->setUpdate('info', $this->text('success_'. $this->_call));
		redirectexit('action='. $this->name .';sa='. $this->_call);
	}

	protected function single()
	{
		// There's gotta be an ID
		if (!$this->_faq)
			fatal_lang_error($this->name .'_noValidId', false);

		// Make sure it does exists...
		$context[$this->name]['single'] = $this->getSingle($this->_faq);

		// Tell the user this entry doesn't exists anymore...
		if (empty($context['current']))
			fatal_lang_error($this->name .'_noValidId', false);
	}

	protected function manage()
	{
		global $context;

		$start = $this->validate('start') ? $this->data('start') : 0;
		$maxIndex = $this->getCount();

		// Quick fix.
		$that = $this;

		// Lets use SMF's createList...
		$listOptions = array(
			'id' => 'faq_manage',
			'title' => $context['page_title'],
			'base_href' => $this->scriptUrl . '?action='. $this->name .';sa='. $this->_call,
			'items_per_page' => 10,
			'get_count' => array(
				'function' => function () use ($maxIndex)
				{
					return $maxIndex;
				},
			),
			'get_items' => array(
				'function' => function ($start, $maxIndex) use ($that)
				{
					global $smcFunc;

					$return = array();
					$request = $smcFunc['db_query']('', '' . ($that->_queryConstruct) . '
						LIMIT {int:start}, {int:maxindex}
						',
						array(
							'start' => $start,
							'maxindex' => $maxIndex,
						)
					);

					while ($row = $smcFunc['db_fetch_assoc']($request))
						$return[$row['id']] = $that->returnData($row);

					$smcFunc['db_free_result']($request);

					return $return;
				},
				'params' => array(
					$start,
					$maxIndex,
				),
			),
			'no_items_label' => $this->text('no_faq'),
			'columns' => array(
				'title' => array(
					'header' => array(
						'value' => $this->text('title_edit'),
					),
					'data' => array(
						'function' => function ($rowData)
						{
							return $rowData['link'];
						},
						'class' => 'centercol',
					),
				),
				'category' => array(
					'header' => array(
						'value' => $this->text('edit_category'),
					),
					'data' => array(
						'function' => function ($rowData)
						{
							return $rowData['cat']['link'];
						},
						'class' => 'centercol',
					),
				),
				'edit' => array(
					'header' => array(
						'value' => $this->text('edit'),
					),
					'data' => array(
						'function' => function ($rowData)
						{
							return $rowData['crud']['edit'];
						},
					),
				),
				'delete' => array(
					'header' => array(
						'value' => $this->text('delete'),
					),
					'data' => array(
						'function' => function ($rowData)
						{
							return str_replace('| ', '', $rowData['crud']['delete']);
						},
					),
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<a href="'. $this->scriptUrl . '?action='. $this->name .';sa=add">'. $this->text('add_send') .'</a>',
				),
			),
		);

		require_once($this->sourceDir . '/Subs-List.php');
		createList($listOptions);

		unset($that);
	}

	protected function manageCat()
	{
		global $context;

		$start = $this->validate('start') ? $this->data('start') : 0;
		$maxIndex = $this->getCount('cat');

		// Quick fix.
		$that = $this;

		// Lets use SMF's createList...
		$listOptions = array(
			'id' => 'faq_manageCat',
			'title' => $this->text('action_manageCat'),
			'base_href' => $this->scriptUrl . '?action='. $this->name .';sa='. $this->_call,
			'items_per_page' => 10,
			'get_count' => array(
				'function' => function () use ($maxIndex)
				{
					return $maxIndex;
				},
			),
			'get_items' => array(
				'function' => function ($start, $maxIndex) use ($that)
				{
					global $smcFunc;

					$return = array();
					$result = $smcFunc['db_query']('', '
						SELECT '. (implode(', ', $that->_table['cat']['columns'])) .'
						FROM {db_prefix}' . ($that->_table['cat']['table']) .'
						LIMIT {int:start}, {int:maxindex}
						',
						array()
					);

					while ($row = $smcFunc['db_fetch_assoc']($result))
						$return[$row['category_id']] = array(
							'id' => $row['category_id'],
							'name' => $row['category_name'],
						);

					return $return;
				},
				'params' => array(
					$start,
					$maxIndex,
				),
			),
			'no_items_label' => $this->text('no_cat'),
			'columns' => array(
				'id' => array(
					'header' => array(
						'value' => $this->text('edit_id'),
					),
					'data' => array(
						'function' => function ($rowData)
						{
							return $rowData['id'];
						},
						'class' => 'centercol',
					),
				),
				'name' => array(
					'header' => array(
						'value' => $this->text('edit_name'),
					),
					'data' => array(
						'function' => function ($rowData)
						{
							return $rowData['name'];
						},
						'class' => 'centercol',
					),
				),
				'edit' => array(
					'header' => array(
						'value' => $this->text('edit'),
					),
					'data' => array(
						'function' => function ($rowData) use ($that)
						{
							return '<a href="'. $that->scriptUrl .'?action='. $that->name .';sa=editCat;cat='. $rowData['id'] .'">'. $row['title'] .'</a>';
						},
					),
				),
				'delete' => array(
					'header' => array(
						'value' => $this->text('delete'),
					),
					'data' => array(
						'function' => function ($rowData)
						{
							return str_replace('| ', '', $rowData['crud']['delete']);
						},
					),
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<a href="'. $this->scriptUrl . '?action='. $this->name .';sa=add">'. $this->text('add_send') .'</a>',
				),
			),
		);

		require_once($this->sourceDir . '/Subs-List.php');
		createList($listOptions);

		unset($that);
	}
}
