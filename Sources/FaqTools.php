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
	// Fool the system!
	public $name = 'Faq';

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
	protected static $_permissions = array();

	public function __construct()
	{
		$this->setRegistry();

		// Query construct, this is used on all queries
		$this->_queryConstruct = 'SELECT f.'. (implode(', f.', $this->_table['faq']['columns']) .', '. implode(', c.', $this->_table['cat']['columns'])) .'
	FROM {db_prefix}' . ($this->_table['faq']['table']) . ' AS f
		LEFT JOIN {db_prefix}' . ($this->_table['cat']['table']) . ' AS c ON (c.category_id = f.cat_id)';

		if (!isset(self::$_permissions))
			self::$_permissions = array(
				'edit' => allowedTo('faq_edit'),
				'delete' => allowedTo('faq_delete'),
			);
	}

	public function create($data)
	{
		// Clear the cache.
		cache_put_data($this->name .'_latest', '', 60);

		$this->smcFunc['db_insert']('',
			'{db_prefix}'. ($this->_table['faq']['table']),
			array(
				'cat_id' => 'int', 'log' => 'string', 'title' => 'string', 'body' => 'string',
			),
			$data,
			array('id')
		);

		// Return the ID.
		return $this->smcFunc['db_insert_id']('{db_prefix}' . ($this->_table['faq']['table']) . '', 'id');
	}

	public function createCat($data)
	{
		if (empty($data))
			return false;

		// Clear the cache.
		cache_put_data($this->name .'_cats', '', 60);

		$this->smcFunc['db_insert']('',
			'{db_prefix}' . ($this->_table['cat']['table']) . '',
			array(
				'category_name' => 'string-255',
			),
			$data,
			array('category_id')
		);

		// Return the ID.
		return $this->smcFunc['db_insert_id']('{db_prefix}' . ($this->_table['cat']['table']), 'id');
	}

	public function update($data)
	{
		if (empty($data))
			return false;

		// Does the cache has this entry?.
		if (($gotIt = cache_get_data($this->name .'_latest', 120)) != null)
			if (!empty($gotIt[$data['id']]))
				cache_put_data($this->name .'_latest', '', 60);

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_table['faq']['table']) . '
			SET cat_id = {int:cat_id}, log = {string:log}, title = {string:title}, body = {string:body}
			WHERE id = {int:id}',
			$data
		);
	}

	public function updateCat($data)
	{
		if (empty($data))
			return false;

		// Clear the cache.
		cache_put_data($this->name .'_cats', '', 60);

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_table['cat']['table']) . '
			SET category_name = {string:category_name}
			WHERE category_id = {int:id}',
			$data
		);
	}

	public function getLatest($limit = 10)
	{
		// Use the cache when possible.
		if (($return = cache_get_data($this->name .'_latest', 120)) == null)
		{
			$result = $this->smcFunc['db_query']('', '' . ($this->_queryConstruct) . '
				ORDER BY {raw:sort}
				LIMIT {int:limit}',
				array(
					'sort' => 'id DESC',
					'limit' => $limit
				)
			);

			while ($row = $this->smcFunc['db_fetch_assoc']($result))
				$return[$row['id']] = $this->returnData($row);

			$this->smcFunc['db_free_result']($result);

			cache_put_data($this->name .'_latest', $return, 120);
		}

		// Done!
		return !empty($return) ? $return : false;
	}

	public function getSingle($id)
	{
		$result = $this->smcFunc['db_query']('', '' . ($this->_queryConstruct) . '
			WHERE id = ({int:id})
			LIMIT {int:limit}',
			array(
				'id' => (int) $id,
				'limit' => 1
			)
		);

		$row = $this->smcFunc['db_fetch_assoc']($result);
		$return = $this->returnData($row);

		$this->smcFunc['db_free_result']($result);

		// Done?
		return !empty($return) ? $return : false;
	}

	public function getBy($page = '', $table, $column, $value, $limit = false, $like = false, $sort = 'title ASC')
	{
		if ($like)
			$likeString = !empty($like) && $like == true ? 'LIKE' : '=';

		// We actually need some stuff to work on...
		if (empty($table) || empty($column) || !in_array($column, $this->_table[$table]['columns']) || empty($value))
			return false;

		$return = array();

		$result = $this->smcFunc['db_query']('', '' . ($this->_queryConstruct) . '
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

		while ($row = $this->smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = $this->returnData($row);

		$this->smcFunc['db_free_result']($result);

		// Done!
		return !empty($return) ? $return : false;
	}

	public function getAll($page = '')
	{
		global $context;

		$total = $this->getCount();
		$maxIndex = $this->enable('num_faqs') ? $this->setting('num_faqs') : 20;
		$sort = $this->enable('sort_method') ? $this->setting('sort_method') : 20;

		$result = $this->smcFunc['db_query']('', '' . ($this->_queryConstruct) . '
			ORDER BY {raw:sort} ASC
			LIMIT {int:start}, {int:maxindex}',
			array(
				'start' => $this->validate('start') ? (int) $this->data('start') : 0,
				'maxindex' => $maxIndex,
				'sort' => $sort
			)
		);

		while ($row = $this->smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = $this->returnData($row);

		$this->smcFunc['db_free_result']($result);

		/* Build the pagination */
		$context['page_index'] = constructPageIndex($this->scriptUrl . '?action='. $this->name . (!empty($page) ? ';sa='. $page .'' : ''), $this->data('start'), $total, $maxIndex, false);

		// Done!
		return !empty($return) ? $return : false;
	}

	protected function getCount($table = 'faq')
	{
		$result = $this->smcFunc['db_query']('', '
			SELECT id
			FROM {db_prefix}' . ($this->_table[$table]['table']),
			array()
		);

		return $this->smcFunc['db_num_rows']($result);
	}

	public function getCats()
	{
		// Use the cache when possible.
		if (($return = cache_get_data($this->name .'_cats', 120)) == null)
		{
			$result = $this->smcFunc['db_query']('', '
				SELECT '. (implode(', ', $this->_table['cat']['columns'])) .'
				FROM {db_prefix}' . ($this->_table['cat']['table']) .'',
				array()
			);

			while ($row = $this->smcFunc['db_fetch_assoc']($result))
				$return[$row['category_id']] = array(
					'id' => $row['category_id'],
					'name' => $row['category_name'],
				);

			$this->smcFunc['db_free_result']($result);

			cache_put_data($this->name .'_cats', $return, 120);
		}

		return $return;
	}

	public function erase($id)
	{
		// Do not waste my time...
		if (empty($id))
			return false;

		// Does the cache had this entry?
		if (($gotIt = cache_get_data($this->name .'_latest', 120)) != null)
			if (!empty($gotIt[$id]))
				cache_put_data($this->name .'_latest', '', 60);

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_table[$table]['table']) .'
			WHERE '. ($table == $this->name ? 'id' : 'category_id') .' = {int:id}',
			array(
				'id' => (int) $id,
			)
		);
	}

	public function createLog($log = array())
	{
		global $user_info;

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
		if (empty($row))
			return array();

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
				'edit' => (self::$_permissions['edit'] ? '<a href="'. $this->scriptUrl .'?action='. $this->name .';sa=edit;faq='. $row['id'] .';edit">'. $this->text('edit') .'</a>' : ''),
				'delete' => ((self::$_permissions['edit'] == true ? ' | ': '') . (self::$_permissions['delete'] ? '<a href="'. $this->scriptUrl .'?action='. $this->name .';sa=delete;faq='. $row['id'] .';" class="you_sure">'. $this->text('delete') .'</a>' : '')),
			),
		);
	}
}
