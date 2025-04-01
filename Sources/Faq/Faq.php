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
                        'href' => $this->buildUrl(FaqController::ACTION, FaqController::SUB_ACTION_ADD),
                        'show' => allowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD),
                    ],
                    'faq_category' => [
                        'title' => $this->utils->text('category_index_title'),
                        'href' => $this->buildUrl(CategoryController::ACTION, CategoryController::SUB_ACTION_MANAGE),
                        'show' => allowedTo([
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD,
                            Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE]),
                        'sub_buttons' => [],
                    ],
                    'faq_manage' => [
                        'title' => $this->utils->text('manage_title'),
                        'href' => $this->buildUrl(FaqController::ACTION, FaqController::SUB_ACTION_MANAGE),
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
