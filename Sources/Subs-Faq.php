<?php

/**
 * @package FAQ mod
 * @version 2.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (C) 2013
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('No direct access');


class Faq
{
	protected $queryConstruct = '';
	protected $_table = array(
		'faq' => array(
			'table' => 'faq',
			'columns' => array('id', 'cat_id', 'log', 'title', 'body', 'last_time',),
		),
		'cat' => array(
			'table' => 'faq_categories',
			'columns' => array('category_id', 'category_log', 'category_name',),
		),
	);

	public static $name = 'faq';

	public function __construct()
	{
		// Query construct, this is used on all queries
		$this->queryConstruct = 'SELECT f.'. (implode(', f.', $this->_table['faq']['columns']) .', '. implode(', c.', $this->_table['cat']['columns'])) .'
			FROM {db_prefix}' . ($this->_table['faq']['table']) . ' AS f
				LEFT JOIN {db_prefix}' . ($this->_table['cat']['table']) . ' AS c ON (c.category_id = f.cat_id)';
	}

	public function add($data)
	{
		global $smcFunc;

		/* Clear the cache */
		cache_put_data(faq::$name .'_latest', '', 60);

		$smcFunc['db_insert']('',
			'{db_prefix}faq',
			array(
				'cat_id' => 'int', 'log' => 'string-65534', 'title' => 'string-255', 'body' => 'string-65534',
			),
			$data,
			array('id')
		);

		/* Set the ID */
		$id = $smcFunc['db_insert_id']('{db_prefix}faq', 'id');

		/* Create the log */
		$this->createLog($id);
	}

	public function edit($data, $table)
	{
		global $smcFunc;

		if (empty($data) || empty($table))
			return false;

		$set = $table == faq::$name ? 'cat_id = {int:cat_id}, log = {string:log}, last_time = {int:last_time} title = {string:title}, body = {string:body}' : 'category_log = {int:category_log}, category_name = {string:category_name}';

		/* Does the cache has this entry? */
		if (($gotIt = cache_get_data(faq::$name .'_latest', 120)) != null)
			if (!empty($gotIt[$data['id']]))
				cache_put_data(faq::$name .'_latest', '', 60);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_table[$table]['table']) . '
			SET '. ($set) .'
			WHERE id = {int:id}',
			$data
		);
	}

	public function getLatest($limit = 10)
	{
		global $smcFunc, $scripturl, $txt;

		 /* Use the cache when possible */
		if (($return = cache_get_data(faq::$name .'_latest', 120)) == null)
		{
			$result = $smcFunc['db_query']('', '' . ($this->queryConstruct) . '
				ORDER BY {raw:sort}
				LIMIT {int:limit}',
				array(
					'sort' => 'id DESC',
					'limit' => $limit
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($result))
				$return[$row['id']] = array(
					'id' => $row['id'],
					'title' => $row['title'],
					'link' => '<a href="'. $scripturl .'?action='. faq::$name .';sa=single;fid='. $this->clean($row['id']) .'">'. $row['title'] .'</a>',
					'body' => parse_bbc($row['body']),
					'preview' => $this->truncateString(parse_bbc($row['body']), 50, $break = ' ', $pad = '...'),
					'cat' => array(
						'id' => $row['category_id'],
						'name' => $row['category_name'],
						'link' => '<a href="'. $scripturl .'?action='. faq::$name .';sa=categories;fid='. $this->clean($row['category_id']) .'">'. $row['category_name'] .'</a>',
					),
					'time' => $row['last_time'],
					'user' => array(
						'id' => $row['user'],
						'username' => $row['member_name'],
						'name' => isset($row['real_name']) ? $row['real_name'] : '',
						'href' => $scripturl . '?action=profile;u=' . $row['user'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['user'] . '" title="' . $txt['profile_of'] . ' ' . $row['real_name'] . '">' . $row['real_name'] . '</a>',
					),
				);

			$smcFunc['db_free_result']($result);

			cache_put_data(faq::$name .'_latest', $return, 120);
		}

		/* Done? */
		return !empty($return) ? $return : false;
	}

	public function getSingle($id)
	{
		global $smcFunc, $scripturl, $txt;

		$result = $smcFunc['db_query']('', '' . ($this->queryConstruct) . '
			WHERE id = ({int:id})
			LIMIT {int:limit}',
			array(
				'id' => (int) $id,
				'limit' => 1
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'link' => '<a href="'. $scripturl .'?action='. faq::$name .';sa=single;fid='. $this->clean($row['id']) .'">'. $row['title'] .'</a>',
				'body' => parse_bbc($row['body']),
				'preview' => $this->truncateString(parse_bbc($row['body']), 50, $break = ' ', $pad = '...'),
				'cat' => array(
					'id' => $row['category_id'],
					'name' => $row['category_name'],
					'link' => '<a href="'. $scripturl .'?action='. faq::$name .';sa=categories;fid='. $this->clean($row['category_id']) .'">'. $row['category_name'] .'</a>'
				),
				'time' => $row['last_time'],
				'user' => array(
					'id' => $row['user'],
					'username' => $row['member_name'],
					'name' => isset($row['real_name']) ? $row['real_name'] : '',
					'href' => $scripturl . '?action=profile;u=' . $row['user'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['user'] . '" title="' . $txt['profile_of'] . ' ' . $row['real_name'] . '">' . $row['real_name'] . '</a>',
				),
			);

		$smcFunc['db_free_result']($result);

		/* Done? */
		return !empty($return) ? $return : false;
	}

	public function getBy($table, $column, $value, $sort = 'title ASC', $limit = false, $like = false)
	{
		global $smcFunc, $scripturl, $txt;

		if (!empty($like) && $like == true)
			$likeString = !empty($like) && $like == true ? 'LIKE' : '=';

		/* We actually need some stuff to work on... */
		if (empty($table) || empty($column) || !in_array($column, $this->_table[$table]['columns']) || empty($value))
			return false;

		$return = array();

		$result = $smcFunc['db_query']('', '' . ($this->queryConstruct) . '
			WHERE '. $column .' '. (is_int($value) ? '= {int:value} ' : $likeString .' {string:value} ') .'
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
			$return[$row['id']] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'link' => '<a href="'. $scripturl .'?action='. faq::$name .';sa=single;fid='. $this->clean($row['id']) .'">'. $row['title'] .'</a>',
				'body' => parse_bbc($row['body']),
				'preview' => $this->truncateString(parse_bbc($row['body']), 50, $break = ' ', $pad = '...'),
				'cat' => array(
					'id' => $row['category_id'],
					'name' => $row['category_name'],
					'link' => '<a href="'. $scripturl .'?action='. faq::$name .';sa=categories;fid='. $this->clean($row['category_id']) .'">'. $row['category_name'] .'</a>'
				),
				'time' => $row['last_time'],
				'user' => array(
					'id' => $row['user'],
					'username' => $row['member_name'],
					'name' => isset($row['real_name']) ? $row['real_name'] : '',
					'href' => $scripturl . '?action=profile;u=' . $row['user'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['user'] . '" title="' . $txt['profile_of'] . ' ' . $row['real_name'] . '">' . $row['real_name'] . '</a>',
				),
			);

		$smcFunc['db_free_result']($result);

		/* Done? */
		return !empty($return) ? $return : false;
	}

	public function getAll($page = '')
	{
		global $smcFunc, $scripturl, $txt, $modSettings, $context;

		$total = $this->getCount();
		$maxIndex = !empty($modSettings['faq_pag_limit']) ? $modSettings['faq_pag_limit'] : 20;

		/* Safety first! */
		$sortArray = array('title', 'artist', 'latest');

		$result = $smcFunc['db_query']('', '' . ($this->queryConstruct) . '
			ORDER BY {raw:sort} ASC
			LIMIT {int:start}, {int:maxindex}',
			array(
				'start' => $_REQUEST['start'],
				'maxindex' => $maxIndex,
				'sort' => 'title'
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'link' => '<a href="'. $scripturl .'?action='. faq::$name .';sa=single;fid='. $this->clean($row['id']) .'">'. $row['title'] .'</a>',
				'body' => parse_bbc($row['body']),
				'preview' => $this->truncateString(parse_bbc($row['body']), 50, $break = ' ', $pad = '...'),
				'cat' => array(
					'id' => $row['category_id'],
					'name' => $row['category_name'],
					'link' => '<a href="'. $scripturl .'?action='. faq::$name .';sa=categories;fid='. $this->clean($row['category_id']) .'">'. $row['category_name'] .'</a>'
				),
				'time' => $row['last_time'],
				'user' => array(
					'id' => $row['user'],
					'username' => $row['member_name'],
					'name' => isset($row['real_name']) ? $row['real_name'] : '',
					'href' => $scripturl . '?action=profile;u=' . $row['user'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['user'] . '" title="' . $txt['profile_of'] . ' ' . $row['real_name'] . '">' . $row['real_name'] . '</a>',
				),
			);

		$smcFunc['db_free_result']($result);

		/* Build the pagination */
		$context['page_index'] = constructPageIndex($scripturl . '?action='. faq::$name . (!empty($page) ? ';sa='. $page .'' : ''), $_REQUEST['start'], $total, $maxIndex, false);

		/* Done? */
		return !empty($return) ? $return : false;
	}

	protected function getCount($table = 'faq')
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT id
			FROM {db_prefix}' . ($this->_table[$table]['table']),
			array()
		);

		return $smcFunc['db_num_rows']($result);
	}

	public function delete($id, $table)
	{
		global $smcFunc;

		if (empty($id) || empty($table))
			return false;

		/* Does the cache has this entry? */
		if ($table == faq::$name && ($gotIt = cache_get_data(faq::$name .'_latest', 120)) != null)
			if (!empty($gotIt[$id]))
				cache_put_data(faq::$name .'_latest', '', 60);

		/* Do not waste my time... */
		if (empty($id) || empty($table))
			return false;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_table[$table]['table']) .'
			WHERE id = {int:id}',
			array(
				'id' => (int) $id,
			)
		);
	}

	public function getCats()
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_table['cat']['columns'])) .'
			FROM {db_prefix}' . ($this->_table['cat']['table']) .'',
			array()
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['category_id']] = array(
			'id' => $row['category_id'],
			'log' => $row['category_log'],
			'name' => $row['category_name'],
			);

		$smcFunc['db_free_result']($result);

		return $return;
	}

	public function clean($string, $body = false)
	{
		global $smcFunc, $sourcedir;

		$string = $smcFunc['htmlspecialchars']($smcFunc['htmltrim']($string, ENT_QUOTES, ENT_QUOTES));

		if ($body)
		{
			require_once($sourcedir.'/Subs-Post.php');
			preparsecode($string);
		}

		return $string;
	}

	public function permissions($type, $fatal_error = false)
	{
		global $modSettings;

		$type = is_array($type) ? array_unique($type) : array($type);
		$allowed = array();

		if (empty($type))
			return false;

		/* The mod must be enable */
		if (empty($modSettings['faqmod_settings_enable']))
			fatal_lang_error('faq_error_enable', false);

		/* Collect the permissions */
		foreach ($type as $t)
			$allowed[] = (allowedTo('faq_'. $t) == true ? 1 : 0);

		/* You need at least 1 permission to be true */
		if ($fatal_error == true && !in_array(1, $allowed))
			isAllowedTo('faq_'. $t);

		elseif ($fatal_error == false && !in_array(1, $allowed))
			return false;

		elseif ($fatal_error == false && in_array(1, $allowed))
			return true;
	}

	/* Creates simple links to edit/delete based on the users permissions */
	public function crud($id)
	{
		global $scripturl, $txt;

		/* By default lets send nothing! */
		$return = '';

		/* We need an ID... */
		if (empty($id))
			return !empty($return) ? $return : false;

		/* Set the pertinent permissions */
		$edit = $this->permissions('edit');
		$delete = $this->permissions('delete');

		/* Let's check if you have what it takes... */
		if ($edit == true)
			$return .= '<a href="'. $scripturl .'?action='. faq::$name .';sa=edit;fid='. $this->clean($id) .'">'. $txt['faqmod_edit_edit'] .'</a>';

		if ($delete == true)
			$return .= ($edit == true ? ' | ': '') .'<a href="'. $scripturl .'?action='. faq::$name .';sa=delete;fid='. $this->clean($id) .'">'. $txt['faq_delete'] .'</a>';

		/* Send the string */
		return !empty($return) ? $return : false;
	}

	public function truncateString($string, $limit, $break = ' ', $pad = '...')
	{
		if(empty($limit))
			$limit = 30;

		 /* return with no change if string is shorter than $limit */
		if(strlen($string) <= $limit)
			return $string;

		/* is $break present between $limit and the end of the string? */
		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}
}
