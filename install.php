<?php

/**
 * Faq mod (SMF)
 *
 * @package Faq
 * @version 2.0
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2025  Michel Mendiola
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
    require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
    exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc, $context, $db_prefix;


db_extend('packages');

if (!empty($context['uninstalling'])) {
    return;
}

$tables[] = [
    'table_name' => 'faq',
    'columns' => [
        [
            'name' => 'id',
            'type' => 'int',
            'size' => 5,
            'null' => false,
            'auto' => true
        ],
        [
            'name' => 'cat_id',
            'type' => 'int',
            'size' => 5,
            'null' => false
        ],
        [
            'name' => 'log',
            'type' => 'text',
            'size' => '',
            'default' => '',
        ],
        [
            'name' => 'title',
            'type' => 'varchar',
            'size' => 255,
            'default' => '',
        ],
        [
            'name' => 'body',
            'type' => 'text',
            'size' => '',
            'default' => '',
        ],
    ],
    'indexes' => [
        [
            'type' => 'primary',
            'columns' => ['id']
        ],
    ],
    'if_exists' => 'ignore',
    'error' => 'fatal',
    'parameters' => [],
];
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
    if (in_array($real_prefix . $table['table_name'], $current_tables)) {
        continue;
    }

    else
        $smcFunc['db_create_table'](
            $db_prefix . $table['table_name'],
            $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);
}

// Give them a default category for good measure
$rows = [];
$rows[] = [
    'method' => 'ignore',
    'table_name' => '{db_prefix}faq_categories',
    'columns' => [
        'category_id' => 'int',
        'category_name' => 'string',
    ],
    'data' => [
        1,
        'Default'
    ],
    'keys' => [
        'category_id'
    ]
];

// Add rows to any existing tables
foreach ($rows as $row)
    $smcFunc['db_insert']($row['method'], $row['table_name'], $row['columns'], $row['data'], $row['keys']);

