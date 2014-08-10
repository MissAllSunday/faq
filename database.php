<?php

/**
 * @package FAQ mod
 * @version 2.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license https://www.mozilla.org/MPL/2.0/
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $smcFunc, $context, $db_prefix;

	db_extend('packages');

	if (empty($context['uninstalling']))
	{
		$tables[] = array(
			'table_name' => 'faq',
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'cat_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'log',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
				array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'body',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);
		$tables[] = array (
			'table_name' => 'faq_categories',
			'columns' => array(
				array(
					'name' => 'category_id',
					'type' => 'int',
					'size' => 11,
					'auto' => true,
				),
				array(
					'name' => 'category_name',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array(
						'category_id'
					),
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		// Now the tables ... if they don't exist create them and if they do exist update them if required.
		$current_tables = $smcFunc['db_list_tables'](false, '%faq%');
		$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;

		// Loop through each defined table and do whats needed, update existing or add as new
		foreach ($tables as $table)
		{
			// Does the table exist?
			if (in_array($real_prefix . $table['table_name'], $current_tables))
			{
				foreach ($table['columns'] as $column)
				{
					if ($column['name'] == 'category_id')
						$smcFunc['db_change_column'](
							'{db_prefix}faq',
							'category_id',
							array(
								'name' => 'cat_id',
								'type' => 'int',
								'size' => 5,
								'null' => false,
							),
							'fatal',
							 array()
						);

					if ($column['name'] != 'log')
						$smcFunc['db_add_column'](
							'{db_prefix}faq',
							array(
								'name' => 'log',
								'type' => 'text',
								'size' => '',
								'default' => '',
							),
							'ignore',
							'fatal'
						);
				}

				foreach ($table['indexes'] as $index)
					$smcFunc['db_add_index']($db_prefix . $table['table_name'], $index, array(), 'ignore');
			}
			else
				$smcFunc['db_create_table']($db_prefix . $table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);
		}

		// Give them a default category for good measure
		$rows = array();
		$rows[] = array(
			'method' => 'ignore',
			'table_name' => '{db_prefix}faq_categories',
			'columns' => array(
				'category_id' => 'int',
				'category_name' => 'string',
			),
			'data' => array(
				1,
				'Default'
			),
			'keys' => array(
				'category_id'
			)
		);

		// Add rows to any existing tables
		foreach ($rows as $row)
			$smcFunc['db_insert']($row['method'], $row['table_name'], $row['columns'], $row['data'], $row['keys']);

	}
