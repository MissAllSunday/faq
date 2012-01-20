<?php

/**
 * @package FAQ mod
 * @version 1.2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

class FaqQuery
{
	private static $instance;
	private $temp = array();
	private $Settings = array();
	private $Text = array();
	private $pattern;
	private $valid;
	private $GetFaqs = array();
	private $key = 'id';
	private $query = array();
	private $query_params = array('rows' =>'*');
	private $query_data = array();
	private $matchesSettings = array();
	private $matchesText = array();

	private function __construct()
	{
		FAQ::Load('Faq-Db');

		$this->query = array(
			'faq' => new FaqDb('faq'),
			'category' => new FaqDb('faq_categories')
		);

		$this->Extract();
	}

	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new FaqQuery();
		}

		return self::$instance;
	}

	private function Query($var)
	{
		return $this->query[$var];
	}

	public function Extract()
	{
		global $txt, $modSettings;

		loadLanguage('Faq');

		$this->pattern = '/faqmod_/';

		/* Get only the settings that we need */
		foreach ($modSettings as $km => $vm)
			if (preg_match($this->pattern, $km))
			{
				$km = str_replace('faqmod_', '', $km);
				$this->matchesSettings[$km] = $vm;
			}

			else
				$this->matchesSettings = array();

		$this->Settings = $this->matchesSettings;

		/* Again, this time for $txt. */
		foreach ($txt as $kt => $vt)
			if (preg_match($this->pattern, $kt))
			{
				$kt = str_replace('faqmod_', '', $kt);
				$this->matchesText[$kt] = $vt;
			}

		$this->Text = $this->matchesText;

		/* Done? then we don't need this anymore */
		unset($this->matchesText);
		unset($this->matchesSettings);
	}

	/* Return true if the value do exist, false otherwise, O RLY? */
	public function enable($var)
	{
		if (!empty($this->Settings[$var]))
			return true;
		else
			return false;
	}

	/* Get the requested setting or text string */
	public function get($var, $type)
	{
		if (!empty($this->Text[$var]) && $type == 'Text')
			return $this->Text[$var];

		elseif (!empty($this->Settings[$var]) && $type == 'Settings')
			return $this->Settings[$var];

		else
			return false;
	}

	/* We collect all the faqs here, cache when possible */
	public function GetFaqs()
	{
		if (($this->GetFaqs = cache_get_data('FAQ:GetFaqs', 120)) == null)
		{
			$this->Query('faq')->Params($this->query_params);
			$this->Query('faq')->GetData($this->key);
			$this->GetFaqs = $this->Query('faq')->DataResult();

			cache_put_data('FAQ:GetFaqs', $this->Query('faq')->DataResult(), 120);
		}

		return $this->GetFaqs;
	}

	/* Get all categories, cache when possible */
	public function GetCategories()
	{
		if (($this->GetCategories = cache_get_data('FAQ:GetCategories', 120)) == null)
		{
			$this->Query('category')->Params($this->query_params);
			$this->Query('category')->GetData('category_id');
			$this->GetCategories = $this->Query('category')->DataResult();

			cache_put_data('FAQ:GetCategories', $this->Query('category')->DataResult(), 120);
		}

		return $this->GetCategories;
	}

	/* Easy way to get what we want */
	private function GetReturn($type, $row, $value)
	{
		/* Get the raw data */
		switch ($type)
		{
			case 'GetFaqs':
				$this->temp = $this->GetFaqs();
				break;
			case 'GetCategories':
				$this->temp = $this->GetCategories();
				break;
		}

		/* Needs to be empty by default */
		$this->r = array();

		/* Do this only if there is something to work with */
		if ($this->temp)
		{
			/* Generate an array with a defined key */
			foreach($this->temp as $t)
				if ($t[$row] == $value)
					$this->r[] = $t;
		}

		/* Return the info we want as we want it */
		unset($this->temp);
		return $this->r;
	}

	/* Count either all the faqs or all the categories */
	private function Count($type)
	{
		if ($type == 'GetFaqs')
			return count($this->GetFaqs());

		elseif ($type == 'GetCategories')
			return count($this->GetCategories());
	}

	public function ShowFaqs()
	{
		global $context, $scripturl, $smcFunc;

		FAQ::Load('Subs');

		$faqmod_num_faqs = $this->enable('num_faqs') ? $this->get('num_faqs', 'Settings') : 100;
		$faqmod_sort = $this->enable('sort_method') ? $this->get('sort_method', 'Settings') : 'id';
		$total_faqs = $this->Count('GetFaqs');

		/* Better let this to $smcFunc */
		$queryT = $smcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}faq
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:maxindex}',
			array(
				'start' => $_REQUEST['start'],
				'maxindex' => $faqmod_num_faqs,
				'sort' => $faqmod_sort,
			)
		);

		$GetFaqsTemp = array();

		while($row = $smcFunc['db_fetch_assoc']($queryT))
		{
			$GetFaqsTemp[$row['id']] = $row;
			$GetFaqsTemp[$row['id']]['body'] = $this->GetClean($GetFaqsTemp[$row['id']]['body']);
			$GetFaqsTemp[$row['id']]['body'] = parse_bbc($GetFaqsTemp[$row['id']]['body']);
			$context['GetFaqs'][] = $GetFaqsTemp[$row['id']];
		}

		$smcFunc['db_free_result']($queryT);
		$context['page_index'] = constructPageIndex($scripturl . '?action=faq', $_REQUEST['start'], $total_faqs,$faqmod_num_faqs, false);

	}

	/* Get the FAQs by category, */
	function GetFaqsbyCat($CatID = '')
	{
		if (empty($CatID))
			return false;

		else
		return $this->GetReturn('GetFaqs', 'category_id', $CatID);
	}

	/* // Editing. */
	function editFaq2($FaqID = '', $user)
	{
		/* Kill the cache */
		cache_put_data('FAQ:GetFaqs', '');

		/* Categories too */
		cache_put_data('FAQ:GetCategories', '');

		/* Using superglobals like this is fugly... but me is lazy enough to not build a sanitize class... maybe later*/
		if (empty($_POST['title']))
			fatal_lang_error('faqmod_no_title', false);

		if (empty($_POST['body']))
			fatal_lang_error('faqmod_no_body', false);

		if (empty($_POST['category_id']))
			fatal_lang_error('faqmod_no_category', false);

		$faqmod_body = $this->FaqClean($_POST['body'], true);
		$faqmod_title = $this->FaqClean($_POST['title'], false);
		$faqmod_category = (int) $_POST['category_id'];

		/* // Track the last person who edited this... */
		$faqmod_last_user = (int) $user;

		/* Don't ask questions... just do what I say! */
		$this->query_params = array(
			'set' =>'title={string:title}, body={string:body}, category_id={int:category_id}, last_user={int:last_user}',
			'where' => 'id = {int:id}',
		);

		$this->query_data = array(
			'title' => $faqmod_title,
			'body' => $faqmod_body,
			'category_id' => $faqmod_category,
			'last_user' => $faqmod_last_user,
			'id' => $FaqID
		);

		$this->Query('faq')->Params($this->query_params, $this->query_data);
		$this->Query('faq')->UpdateData();
	}

	/* // Bye bye FAQ... */
	function delete2($FaqID = '')
	{
		/* Kill the cache */
		cache_put_data('FAQ:GetFaqs', '');

		/* Categories too */
		cache_put_data('FAQ:GetCategories', '');

		$this->query_params = array(
			'where' => 'id = {int:id}'
		);

		$this->query_data = array(
			'id' => $FaqID
		);

		$this->Query('faq')->Params($this->query_params, $this->query_data);
		$this->Query('faq')->DeleteData();
	}

	function add2($user)
	{
		/* // No title/body no cookie... */
		if (empty($_POST['title']))
			fatal_lang_error('faqmod_no_title', false);

		if (empty($_POST['body']))
			fatal_lang_error('faqmod_no_body', false);

		if (empty($_POST['category_id']))
			fatal_lang_error('faqmod_no_category', false);

		/* // Cleaning up the mess */
		$faqmod_body = $this->FaqClean($_POST['body'], true);
		$faqmod_title = $this->FaqClean($_POST['title'], false);
		$faqmod_category = (int) $_POST['category_id'];

		/* // Track the person who created this... */
		$faqmod_last_user = (int) $user;

		/* Kill the cache */
		cache_put_data('FAQ:GetFaqs', '');

		/* Categories too */
		cache_put_data('FAQ:GetCategories', '');

		$data = array(
			'title' => 'string',
			'body' => 'string',
			'category_id' => 'int',
			'last_user' => 'int'
		);
		$values = array(
			$faqmod_title,
			$faqmod_body,
			$faqmod_category,
			$faqmod_last_user
		);
		$indexes = array(
			'id'
		);

		/* Insert! */
		$this->Query('faq')->InsertData($data, $values, $indexes);
	}

	/* // Edit a category */
	function editCat2($CatID = '', $user)
	{
		/* // No name no cookie... */
		if (empty($_POST['category_name']))
			fatal_lang_error('faqmod_no_category_name', false);

		/* // Cleaning up the mess */
		$cat_name = $this->FaqClean($_POST['category_name'], false);

		/* // Track the last person who edited this category... */
		$cat_last_user = (int) $user;

		/* Kill the cache */
		cache_put_data('FAQ:GetCategories', '');


		/* // Update, do it quickly, I got more things to do you know... */
		$this->query_params = array(
			'set' =>'category_name={string:category_name}, category_last_user={int:category_last_user}',
			'where' => 'category_id = {int:id}',
		);

		$this->query_data = array(
			'category_name' => $cat_name,
			'id' => (int) $CatID,
			'category_last_user' => $cat_last_user
		);

		$this->Query('category')->Params($this->query_params, $this->query_data);
		$this->Query('category')->UpdateData();
	}

	/* ~I just can't get enough, I just can get enough~ */
	function addCat2($user)
	{
		// No name no cookie...
		if (empty($_POST['category_name']))
			fatal_lang_error('faqmod_no_category_name', false);

		$cat_name = $this->FaqClean($_POST['category_name'],false);
		$category_last_user = (int) $user;

		/* Kill the cache */
		cache_put_data('FAQ:GetCategories', '');

		/* Insert! */
		$data = array(
			'category_name' => 'string',
			'category_last_user' => 'int'
		);
		$values = array(
			$cat_name,
			$category_last_user
		);
		$indexes = array(
			'category_id'
		);

		$this->Query('category')->InsertData($data, $values, $indexes);
	}

	/* Delete a category */
	function deleteCat2($CatID = '')
	{
		/* Kill the cache */
		cache_put_data('FAQ:GetCategories', '');

		$this->query_params = array(
			'where' => 'category_id = {int:id}'
		);

		$this->query_data = array(
			'id' => $CatID
		);

		$this->Query('category')->Params($this->query_params, $this->query_data);
		$this->Query('category')->DeleteData();
	}

	// Show me that you care
	function FaqCare()
	{
		$faqmod_care = '<div class="smalltext" style="text-align:center;">
<a href="http://missallsunday.com" target="_blank" title="Free SMF mods">FAQ mod &copy; Suki</a>
</div>';

		return $faqmod_care;
	}

	function FaqClean($toclean, $body = false)
	{
		global $smcFunc;

		$toclean = $smcFunc['htmlspecialchars']($toclean, ENT_QUOTES);
		$toclean = $smcFunc['htmltrim']($toclean, ENT_QUOTES);

		if ($body){

			FAQ::Load('Subs-Post');
			preparsecode($toclean);
		}

		return $toclean;
	}

	function GetClean($togetclean)
	{
		FAQ::Load('Subs-Post');

		$togetclean = un_preparsecode($togetclean);

		return $togetclean;
	}

	/* // Get the user's data */
	public function GetUser($userid)
	{
		global $scripturl, $memberContext, $context;

		if (empty($userid))
			return $this->get('na', 'Text');

		if (isset($context['FAQ']['user'][$userid]) && !empty($context['FAQ']['user'][$userid]))
			return $context['FAQ']['user'][$userid];

		else
		{
			$user = $userid;
			loadMemberData($user, false, 'profile');
			loadMemberContext($user);
			$user = $memberContext[$user];
			$context['FAQ']['user'][$userid] = '<a href="'.$scripturl.'?action=profile;u='.$userid.'">'.$user['name'].'</a>';

			return $context['FAQ']['user'][$userid];
		}
	}
}
	/* // Believe in you. */