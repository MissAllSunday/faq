<?php

declare(strict_types=1);

namespace Faq;

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;

class Faq
{
	public const NAME = 'faq';
    protected FaqUtils $utils;

    public function __construct(?FaqUtils $utils = null)
    {
        $this->utils = $utils ?? new FaqUtils();
    }

    public function menu(array &$menu_buttons): void
    {
        global $scripturl, $context;

        if (!$this->utils->setting(FaqAdmin::SETTINGS_ENABLE)) {
            return;
        }

        $menuReference = $this->utils->setting(
            FaqAdmin::SETTINGS_MENU_POSITION,
            $this->utils->smfText('home'));
        $counter = 0;

        foreach (array_keys($menu_buttons) as $area) {
            $counter++;
            if ($area === $menuReference) {
                break;
            }
        }

        $menu_buttons = array_merge(
            array_slice($menu_buttons, 0, $counter),
            [Faq::NAME => [
                'title' => $this->utils->text('title_main'),
                'href' => $this->buildUrl(FaqController::ACTION),
                'show' => allowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW),
                'sub_buttons' => [
                    'faq_add' => [
                        'title' => $this->utils->text('add_title'),
                        'href' => $this->buildUrl(FaqController::ACTION, FaqController::SUB_ACTIONS[1]),
                        'show' => allowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD),
                    ],
                    'faq_category' => [
                        'title' => $this->utils->text('manage_categories'),
                        'href' => $this->buildUrl(CategoryController::ACTION, CategoryController::SUB_ACTIONS[1]),
                        'show' => allowedTo([
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD,
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE]),
                        'sub_buttons' => [],
                    ],
                    'faq_admin' => [
                        'title' => $this->utils->text('admin_panel'),
                        'href' => $scripturl . '?action=admin;area=' . Faq::NAME,
                        'show' => allowedTo('admin_forum'),
                    ],
                ],
            ]],
            array_slice($menu_buttons, $counter)
        );
    }

    protected function buildUrl(string $action, ?string $subAction = null): string
    {
        global $scripturl;

        return strtr('{url}?action={action}{subAction}', [
            '{url}' => $scripturl,
            '{action}' => $action,
            '{subAction}' => $subAction ? (';sa=' . $subAction) : '',
        ]);
    }

	public function call()
	{
		global $context;
		static $_permissions = array();

		if (empty($_permissions))
			foreach ($this->_checkPerm as $p)
				$_permissions[$p] = allowedTo('faq_'. $p);

		// The mod needs to be enable.
		if (!$this->setting('enable'))
			return redirectexit();

		$sa = $this->data('sa');

		// Get the subaction.
		$call = $this->_call = in_array($sa, $this->subActions) ? $sa : 'main';

		// There are a few calls visible for guest.
		if (!in_array($call, array('main', 'single')))
			checkSession('request');

		// Check if the user can actually do whatever the user is trying to!
		if (in_array($call, $this->_checkPerm))
			isAllowedTo('faq_'. $this->_call);

		// Load both language and template files.
		loadLanguage($this->name);
		loadtemplate($this->name);

		// Let us declare a fancy and quite useful multi-purpose context array.
		$context[$this->name] = array();

		// Is there any messages? Dunno if there is an error or info message...
		$context[$this->name]['update'] = $this->getAllUpdates();
		$context[$this->name]['permissions'] = $_permissions;
		$context['page_title'] = $this->text('main');

		// The basic linktree, each subaction needs to add their own.
		$context['linktree'][] = array(
			'url' => $this->scriptUrl .'?action='. $this->name,
			'name' => $context['page_title'],
		);

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

		// No cats? then tell the user to go to his/her  nearest animal shelter at once!
		$context[$this->name]['no_cat_admin'] = empty($context[$this->name]['cats']) ? $this->parser($this->text('no_cat_admin'), array('add_cat_href' => $this->scriptUrl . '?action='. $this->name .';sa=manageCat')) : false;

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
			'value' => !empty($context[$this->name]['current']['body']) ? $context[$this->name]['current']['body'] : '',
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
		$context[$this->name]['current'] = $this->getSingle($this->_faq);

		// Tell the user this entry doesn't exists anymore...
		if (empty($context[$this->name]['current']))
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
			'value' => !empty($context[$this->name]['current']['body']) ? $context[$this->name]['current']['body'] : '',
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

		// A default var for previewing.
		$context[$this->name]['current'] = array();

		require_once($this->sourceDir.'/Subs-Post.php');

		// Set everything up to be displayed.
		$context[$this->name]['current'] = $this->data('current');
		$context[$this->name]['current']['body'] = $this->data('body');
		$context['preview'] = $context[$this->name]['current'];

		// We Censor for your protection...
		censorText($context['preview']['title']);
		preparsecode($context[$this->name]['current']['body'], true);
		preparsecode($context['preview']['body']);
		$context['preview']['body'] = parse_bbc($context['preview']['body']);
		censorText($context['preview']['body']);

		// Set a descriptive title.
		$context['page_title'] = $txt['preview'] .' - ' . $context[$this->name]['current']['title'];
	}

	protected function save()
	{
		global $context, $txt;

		checkSession('request');

		require_once($this->sourceDir.'/Subs-Post.php');

		$context[$this->name]['current'] = $this->data('current');
		$context[$this->name]['current']['body'] = $this->data('body');
		$isEmpty = array();

		// You need to enter something!
		if (empty($context[$this->name]['current']))
		{
			// Set everything up to be displayed.
			$context[$this->name]['current'] = $this->data('current');
			preparsecode($context[$this->name]['current']['body'], true);

			// Fool the system, again!
			$context[$this->name]['update']['error'] = $this->text('error_emtpyAll');
			return;
		}

		if (empty($context[$this->name]['current']['body']))
			$isEmpty[] = 'body';

		foreach ($context[$this->name]['current'] as $k => $v)
			if (empty($v))
				$isEmpty[] = $k;

		// Did you forgot something?
		if (!empty($isEmpty))
		{
			preparsecode($context[$this->name]['current']['body'], true);

			// Fool the system, again!
			$context[$this->name]['update']['error'] = str_replace('{fields}', implode(', ', $isEmpty), $this->text('error_emtpyFields'));
			return;
		}

		// Add some more things we need.
		$context[$this->name]['current']['log'] = $this->createLog();
		preparsecode($context[$this->name]['current']['body']);

		// Finally store it.
		$id = $this->create($context[$this->name]['current']);
		$note = (!empty($id) ? 'info' : 'error');

		$this->setUpdate($note, $this->text($note . '_'. $this->_call));

		return redirectexit('action='. $this->name .';sa='. $this->_call);
	}

	protected function delete()
	{
		global $context, $txt;

		if (!$this->_faq)
			fatal_lang_error($this->name .'_noValidId', false);

		// Delete the entry.
		$this->erase($this->_faq);

		// Redirect them to whatever the page they were viewing.
		$this->setUpdate('info', $this->text('info_'. $this->_call));
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
		if (empty($context[$this->name]['current']))
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
					'value' => $this->parser($this->text('crud'), array(
						'href' => $this->scriptUrl . '?action='. $this->name .';sa=add',
						'title' => $this->text('add_send'),
					)),
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

		// Gotta overwrite a few things.
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'faq_manageCat';

		// Quick fix.
		$that = $this;

		// Lets use SMF's createList.
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
						LIMIT {int:start}, {int:maxIndex}
						',
						array(
							'start' => $start,
							'maxIndex' => $maxIndex,
						)
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
							return $that->parser($that->text('crud'), array(
								'href' => $that->scriptUrl .'?action='. $that->name .';sa=addCat;cat='. $rowData['id'],
								'title' => $that->text('edit'),
							));
						},
					),
				),
				'delete' => array(
					'header' => array(
						'value' => $this->text('delete'),
					),
					'data' => array(
						'function' => function ($rowData) use ($that)
						{
							return $that->parser($that->text('delete'), array(
								'href' => $that->scriptUrl .'?action='. $that->name .';sa=deleteCat;cat='. $rowData['id'],
							));
						},
					),
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => $this->parser($this->text('crud'), array(
						'href' => $this->scriptUrl . '?action='. $this->name .';sa=addCat',
						'title' => $this->text('addcat_send')
					)),
				),
			),
		);

		require_once($this->sourceDir . '/Subs-List.php');
		createList($listOptions);

		unset($that);
	}

	protected function addCat()
	{
		global $context;

		// No cat means we are adding one.
		$context['catID'] = $this->validate('cat') ? $this->data('cat') : 0;
		$context['currentCat'] = array();

		// Fix the linktree, page title and all that stuff...

		// So, editing huh? lets get the current cat data. All cats should be loaded already so lets check that first.
		if (!empty($context['catID']))
			$context['currentCat'] = !empty($context[$this->name]['cats'][$context['catID']]) ? $context[$this->name]['cats'][$context['catID']] : $this->getSingleCat($context['catID']);
	}
}
