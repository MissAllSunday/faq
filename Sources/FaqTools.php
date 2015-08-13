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

require_once ($sourcedir .'/ohara/src/Suki/Ohara.php');

class FaqTools extends Suki\Ohara
{
	// Fool the system!
	public $name = 'Faq';

	// Define the hooks we are going to use.
	protected $_availableHooks = array(
		'actions' => 'integrate_actions',
		'menu' => 'integrate_menu_buttons',
		'permissions' => 'integrate_load_permissions',
		'adminAreas' => 'integrate_admin_areas',
		'modifications' => 'integrate_modify_modifications',
	);

	// Hooks points out to different files and classes.
	protected $_overwriteHooks = array(
		'actions' => array(
			'file' => 'Faq.php',
			'func' => 'Faq::addActions',
		),
		'permissions' => array(
			'file' => 'FaqAdmin.php',
			'func' => 'FaqAdmin::addPermissions',
		),
		'adminAreas' => array(
			'file' => 'FaqAdmin.php',
			'func' => 'FaqAdmin::addAdminAreas',
		),
		'modifications' => array(
			'file' => 'FaqAdmin.php',
			'func' => 'FaqAdmin::addModifications',
		),
	);

	protected $_queryConstruct = '';
	protected $_table = array(
		'faq' => array(
			'table' => 'faq',
			'columns' => array('id', 'cat_id', 'log', 'title', 'body',),
		),
		'cat' => array(
			'table' => 'faq_categories',
			'columns' => array('category_id', 'category_name',),
		),
	);
	protected $_checkPerm = array('main', 'delete', 'add', 'edit', 'search');

	public function __construct()
	{
		$this->setRegistry();

		// Query construct, this is used on all queries
		$this->_queryConstruct = 'SELECT f.'. (implode(', f.', $this->_table['faq']['columns']) .', c.'. implode(', c.', $this->_table['cat']['columns'])) .'
	FROM {db_prefix}' . ($this->_table['faq']['table']) . ' AS f
		LEFT JOIN {db_prefix}' . ($this->_table['cat']['table']) . ' AS c ON (c.category_id = f.cat_id)';
	}

	function addMenu(&$menu_buttons)
	{
		global $txt, $context;

		$insert = $this->enable('menuPosition') ? $this->setting('menuPosition') : 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert)
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('faq' => array(
				'title' => $this->text('main'),
				'href' => $this->scriptUrl . '?action='. $this->name,
				'show' => $this->enable('enable') && allowedTo('faq_view') ? true : false,
				'sub_buttons' => array(
					'faq_admin' => array(
						'title' => $this->text('manage'),
						'href' => $this->scriptUrl . '?action='. $this->name .';sa=manage;' .$context['session_var'] .'='. $context['session_id'],
						'show' => allowedTo('faq_edit'),
						'sub_buttons' => array(
							'faq_add' => array(
								'title' => $this->text('add_send'),
								'href' => $this->scriptUrl . '?action='. $this->name .';sa=add;' .$context['session_var'] .'='. $context['session_id'],
								'show' => allowedTo('faq_add'),
							),
						),
					),
					'faq_category' => array(
						'title' => $this->text('manage_categories'),
						'href' => $this->scriptUrl . '?action='. $this->name .';sa=manageCat;' .$context['session_var'] .'='. $context['session_id'],
						'show' => allowedTo(array('faq_delete', 'faq_add', 'faq_edit')),
						'sub_buttons' => array(),
					),
					'faq_admin_settings' => array(
						'title' => $this->text('admin'),
						'href' => $this->scriptUrl . '?action=admin;area=modsettings;sa=faq;' .$context['session_var'] .'='. $context['session_id'],
						'show' => allowedTo('admin_forum'),
						'sub_buttons' => array(),
					),
				),
			)),
			array_slice($menu_buttons, $counter)
		);
	}

	public function create($data)
	{
		global $smcFunc;

		// Clear the cache.
		cache_put_data($this->name .'_latest', '', 60);

		$smcFunc['db_insert']('',
			'{db_prefix}'. ($this->_table['faq']['table']),
			array(
				'title' => 'string', 'cat_id' => 'int', 'body' => 'string', 'log' => 'string',
			),
			$data,
			array('id')
		);

		// Return the ID.
		return $smcFunc['db_insert_id']('{db_prefix}' . ($this->_table['faq']['table']) . '', 'id');
	}

	public function createCat($data)
	{
		global $smcFunc;

		if (empty($data))
			return false;

		// Clear the cache.
		cache_put_data($this->name .'_cats', '', 60);

		$smcFunc['db_insert']('',
			'{db_prefix}' . ($this->_table['cat']['table']) . '',
			array(
				'category_name' => 'string-255',
			),
			$data,
			array('category_id')
		);

		// Return the ID.
		return $smcFunc['db_insert_id']('{db_prefix}' . ($this->_table['cat']['table']), 'id');
	}

	public function update($data)
	{
		global $smcFunc;

		if (empty($data))
			return false;

		// Does the cache has this entry?.
		if (($gotIt = cache_get_data($this->name .'_latest', 120)) != null)
			if (!empty($gotIt[$data['id']]))
				cache_put_data($this->name .'_latest', '', 60);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_table['faq']['table']) . '
			SET cat_id = {int:cat_id}, log = {string:log}, title = {string:title}, body = {string:body}
			WHERE id = {int:id}',
			$data
		);
	}

	public function updateCat($data)
	{
		global $smcFunc;

		if (empty($data))
			return false;

		// Clear the cache.
		cache_put_data($this->name .'_cats', '', 60);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_table['cat']['table']) . '
			SET category_name = {string:category_name}
			WHERE category_id = {int:id}',
			$data
		);
	}

	public function getLatest($limit = 10)
	{
		global $smcFunc;

		// Use the cache when possible.
		if (($return = cache_get_data($this->name .'_latest', 120)) == null)
		{
			$result = $smcFunc['db_query']('', '' . ($this->_queryConstruct) . '
				ORDER BY {raw:sort}
				LIMIT {int:limit}',
				array(
					'sort' => 'id DESC',
					'limit' => $limit
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($result))
				$return[$row['id']] = $this->returnData($row);

			$smcFunc['db_free_result']($result);

			cache_put_data($this->name .'_latest', $return, 120);
		}

		// Done!
		return !empty($return) ? $return : false;
	}

	public function getSingle($id)
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '' . ($this->_queryConstruct) . '
			WHERE id = ({int:id})
			LIMIT {int:limit}',
			array(
				'id' => (int) $id,
				'limit' => 1
			)
		);

		$row = $smcFunc['db_fetch_assoc']($result);
		$return = $this->returnData($row);

		$smcFunc['db_free_result']($result);

		// Done?
		return !empty($return) ? $return : false;
	}

	public function getSingleCat($id)
	{
		global $smcFunc;

		$return = array();
		$result = $smcFunc['db_query']('', '' . (implode(', ', $this->_table['cat']['columns'])) . '
			WHERE '.($this->_table['cat']['columns'][0]).' = ({int:id})
			LIMIT {int:limit}',
			array(
				'id' => (int) $id,
				'limit' => 1
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['category_id']] = array(
				'id' => $row['category_id'],
				'name' => $row['category_name'],
			);

		$smcFunc['db_free_result']($result);

		// Done?
		return !empty($return) ? $return : false;
	}

	public function getBy($page = '', $table, $column, $value, $limit = false, $like = false, $sort = 'title ASC')
	{
		global $smcFunc;

		if ($like)
			$likeString = !empty($like) && $like == true ? 'LIKE' : '=';

		// We actually need some stuff to work on...
		if (empty($table) || empty($column) || !in_array($column, $this->_table[$table]['columns']) || empty($value))
			return false;

		$return = array();

		$result = $smcFunc['db_query']('', '' . ($this->_queryConstruct) . '
			WHERE '. $column .' '. (is_numeric($value) ? '= {int:value} ' : $likeString .' {string:value} ') .'
			ORDER BY {raw:sort}
			'. (!empty($limit) ? '
			LIMIT {int:limit}' : '') .'',
			array(
				'sort' => $sort,
				'value' => $value,
				'column' => $column,
				'limit' => !empty($limit) ? $limit : 0,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = $this->returnData($row);

		$smcFunc['db_free_result']($result);

		// Done!
		return !empty($return) ? $return : false;
	}

	public function getAll($page = '')
	{
		global $context, $smcFunc;

		$total = $this->getCount();
		$maxIndex = $this->enable('num_faqs') ? $this->setting('num_faqs') : 20;
		$sort = $this->enable('sort_method') ? $this->setting('sort_method') : 20;

		$result = $smcFunc['db_query']('', '' . ($this->_queryConstruct) . '
			ORDER BY {raw:sort} ASC
			LIMIT {int:start}, {int:maxindex}',
			array(
				'start' => $this->validate('start') ? (int) $this->data('start') : 0,
				'maxindex' => $maxIndex,
				'sort' => $sort
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = $this->returnData($row);

		$smcFunc['db_free_result']($result);

		/* Build the pagination */
		$context['page_index'] = constructPageIndex($this->scriptUrl . '?action='. $this->name . (!empty($page) ? ';sa='. $page .'' : ''), $this->data('start'), $total, $maxIndex, false);

		// Done!
		return !empty($return) ? $return : false;
	}

	protected function getCount($table = 'faq')
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT ' . ($this->_table[$table]['columns'][0]) .'
			FROM {db_prefix}' . ($this->_table[$table]['table']),
			array()
		);

		return $smcFunc['db_num_rows']($result);
	}

	public function getCats()
	{
		global $smcFunc;

		// Use the cache when possible.
		if (($return = cache_get_data($this->name .'_cats', 120)) == null)
		{
			$result = $smcFunc['db_query']('', '
				SELECT '. (implode(', ', $this->_table['cat']['columns'])) .'
				FROM {db_prefix}' . ($this->_table['cat']['table']) .'',
				array()
			);

			while ($row = $smcFunc['db_fetch_assoc']($result))
				$return[$row['category_id']] = array(
					'id' => $row['category_id'],
					'name' => $row['category_name'],
				);

			$smcFunc['db_free_result']($result);

			cache_put_data($this->name .'_cats', $return, 120);
		}

		return $return;
	}

	public function erase($id)
	{
		global $smcFunc;

		// Do not waste my time...
		if (empty($id))
			return false;

		// Does the cache had this entry?
		if (($gotIt = cache_get_data($this->name .'_latest', 120)) != null)
			if (!empty($gotIt[$id]))
				cache_put_data($this->name .'_latest', '', 60);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_table[$table]['table']) .'
			WHERE '. ($table == $this->name ? 'id' : 'category_id') .' = {int:id}',
			array(
				'id' => (int) $id,
			)
		);
	}

	public function createLog($log = array())
	{
		global $user_info, $smcFunc;

		// If log is empty, it means we are adding.
		if (!$log)
			$log[] = array(
				'user' => $user_info['id'],
				'time' => time(),
			);

		// Handle editing.
		elseif (!empty($log))
		{
			// Gotta unserialize to work with it.
			$log = unserialize($log);

			// If this user already modified this, just update the time.
			if (!empty($log)&& is_array($log))
			{
				foreach ($log as $l)
					if ($l['user'] == $user_info['id'])
					{
						/* Add the new time */
						$log[$l]['time'] = time();
						break;
						return serialize($log);
					}

				// New user huh?.
				$log[] = array(
					'user' => $user_info['id'],
					'time' => time(),
				);
			}
		}

		// Either way, return it.
		return serialize($log);
	}

	protected function returnData($row)
	{
		static $_permissions = array();

		if (empty($row))
			return array();

		if (empty($_permissions))
			foreach ($this->_checkPerm as $p)
				$_permissions[$p] = allowedTo('faq_'. $p);

		return array(
			'id' => $row['id'],
			'title' => $row['title'],
			'link' => '<a href="'. $this->scriptUrl .'?action='. $this->name .';sa=single;faq='. $row['id'] .'" '. ($this->enable('use_js') ? 'class="faq_show" data-faq="'. $row['id'] .'"' : '') .'>'. $row['title'] .'</a>',
			'body' => parse_bbc($row['body']),
			'cat' => array(
				'id' => $row['category_id'],
				'name' => $row['category_name'],
				'link' => '<a href="'. $this->scriptUrl .'?action='. $this->name .';sa=categories;faq='. $row['category_id'] .'">'. $row['category_name'] .'</a>'
			),
			'log' => ($row['log']),
			'crud' => array(
				'edit' => ($_permissions['edit'] ? $this->parser($this->text('edit'), array('href' => $this->scriptUrl .'?action='. $this->name .';sa=edit;faq='. $row['id'] .';edit')) : ''),
				'delete' => (($_permissions['edit'] == true ? ' | ': '') . ($_permissions['delete'] ? $this->parser($this->text('delete'), array('href' => $this->scriptUrl .'?action='. $this->name .';sa=delete;faq='. $row['id'])) : '')),
			),
		);
	}
}
