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

	if (!defined('SMF'))
		die('Hacking attempt...');

	/* A bunch of wrapper functions for SMF */
	function FAQWrapperModifySettings(){ FAQ::ModifyFaqSettings(); }
	function WrapperFaqManage(){ FAQ::FaqManage(); }
	function WrapperFaqEdit(){ FAQ::FaqEdit(); }
	function WrapperFaqEdit2(){ FAQ::FaqEdit2(); }
	function WrapperFaqDelete(){ FAQ::FaqDelete(); }
	function WrapperFaqDelete2(){ FAQ::FaqDelete2(); }
	function WrapperFaqAdd(){ FAQ::FaqAdd(); }
	function WrapperFaqAdd2(){ FAQ::FaqAdd2(); }
	function WrapperFaqManageCat(){ FAQ::FaqManageCat(); }
	function WrapperFaqEditCat(){ FAQ::FaqEditCat(); }
	function WrapperFaqEditCat2(){ FAQ::FaqEditCat2(); }
	function WrapperFaqDeleteCat(){ FAQ::FaqDeleteCat(); }
	function WrapperFaqDeleteCat2(){ FAQ::FaqDeleteCat2(); }
	function WrapperFaqAddCat(){ FAQ::FaqAddCat(); }
	function WrapperFaqAddCat2(){ FAQ::FaqAddCat2(); }
	function WrapperFaqCategory(){ FAQ::FaqCategory(); }
	function WrapperFaqShow(){ FAQ::FaqShow(); }
	function WrapperBasicFaqSettings() { FAQ::BasicFaqSettings(); }
	function WrapperEditFaqAdminPage() { FAQ::EditFaqAdminPage(); }
	function WrapperAddFaqAdminPage() { FAQ::AddFaqAdminPage(); }

abstract class FAQ
{
	public static $faq;

	function __construct(){}

	public static function Load($file)
	{
		global $sourcedir;

		if (empty($file))
			return;

		if (is_array($file) && !empty($file))
				foreach($file as $f)
						require_once($sourcedir.'/'.$f.'.php');

		else
			require_once($sourcedir.'/'.$file.'.php');
	}

	public static function Essential()
	{
		self::Load('Subs-Faq');
		loadtemplate('Faq');

		self::$faq = FaqQuery::getInstance();
		self::$faq->Extract();
	}

	/* Action hook */
	static function FaqAction(&$actions)
	{
		$actions['faq'] = array('Faq.php', 'FAQ::FaqMain');
	}

	/* Permission hook */
	static function FaqPermissions(&$permissionGroups, &$permissionList)
	{
		$permissionGroups['membergroup']['simple'] = array('faqper');
		$permissionGroups['membergroup']['classic'] = array('faqper');
		$permissionList['membergroup']['faqperview'] = array(false, 'faqper', 'faqper');
		$permissionList['membergroup']['faqperedit'] = array(false, 'faqper', 'faqper');
	}

	/* Button menu hook */
	static function FaqMenu(&$menu_buttons)
	{
		global $scripturl;

		self::Essential();

		$faqmod_insert = self::$faq->enable('menu_position') ? self::$faq->get('menu_position', 'Settings') : 'home';

		/* Let's add our button next to the user's selection...
		 * Thanks to SlammedDime (http://mattzuba.com) for the example */
		$counter = 0;
		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $faqmod_insert)
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('faq' => array(
			'title' => self::$faq->get('title_main', 'Text'),
			'href' => $scripturl . '?action=faq',
			'show' => allowedTo('faqperview'),
			'sub_buttons' => array(
				'faqmod_admin' => array(
					'title' => self::$faq->get('manage', 'Text'),
					'href' => $scripturl . '?action=faq;sa=manage',
					'show' => allowedTo('faqperedit'),
					'sub_buttons' => array(
						'faqmod_add' => array(
							'title' => self::$faq->get('add_send', 'Text'),
							'href' => $scripturl . '?action=faq;sa=add',
							'show' => allowedTo('faqperedit'),
						),
					),
				),
				'faqmod_category' => array(
					'title' => self::$faq->get('manage_category', 'Text'),
					'href' => $scripturl . '?action=faq;sa=managecat',
					'show' => allowedTo('faqperedit'),
					'sub_buttons' => array(
						'faqmod_add' => array(
							'title' => self::$faq->get('addcat_send', 'Text'),
							'href' => $scripturl . '?action=faq;sa=addcat',
							'show' => allowedTo('faqperedit'),
						),
					),
				),
			),
		)),
			array_slice($menu_buttons, $counter)
		);
	}

	/* Admin menu hook */
	static function FaqAdmin(&$admin_areas)
	{
		self::Essential();

		$admin_areas['config']['areas']['faqdmin'] = array(
					'label' => self::$faq->get('admin_panel', 'Text'),
					'file' => 'Faq.php',
					'function' => 'FAQWrapperModifySettings',
					'icon' => 'posts.gif',
					'subsections' => array(
						'basic' => array(self::$faq->get('basic_settings', 'Text')),
						'edit' => array(self::$faq->get('edit_page', 'Text')),
						'add' => array(self::$faq->get('add_send', 'Text')),
				),
		);
	}

	/* The settings hook */
	static function ModifyFaqSettings($return_config = false)
	{
		global $scripturl, $context;

		self::Load('ManageSettings');
		self::Essential();

		$context['page_title'] = self::$faq->get('admin_panel', 'Text');

		$subActions = array(
			'basic' => 'WrapperBasicFaqSettings',
			'edit' => 'WrapperEditFaqAdminPage',
			'add' => 'WrapperAddFaqAdminPage',
		);

		loadGeneralSettingParameters($subActions, 'basic');

		// Load up all the tabs...
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => self::$faq->get('admin_panel', 'Text'),
			'description' => self::$faq->get('admin_panel_desc', 'Text') . self::$faq->phpVersion(),
			'tabs' => array(
				'basic' => array(
				),
				'edit' => array(
				),

			),
		);

		$subActions[$_REQUEST['sa']]();
	}

	/* Settings */
	static function BasicFaqSettings()
	{
		global $scripturl, $context;

		self::Load('ManageServer');
		self::Essential();

		$config_vars = array(
			array(
				'int',
				'faqmod_num_faqs',
				'size' => 3,
				'subtext' => self::$faq->get('num_faqs_sub', 'Text')
			),
			array(
				'select',
				'faqmod_sort_method',
				array(
					'id' => self::$faq->get('id', 'Text'),
					'title' => self::$faq->get('title', 'Text'),
					'timestamp' => self::$faq->get('date', 'Text')
				),
				'subtext' => self::$faq->get('sort_method_sub', 'Text')
			),
			array(
				'select',
				'faqmod_menu_position',
				array(
					'home' => self::$faq->get('menu_home', 'Text'),
					'help' => self::$faq->get('menu_help', 'Text'),
					'search' => self::$faq->get('menu_search', 'Text'),
					'login' => self::$faq->get('menu_login', 'Text'),
					'register' => self::$faq->get('menu_register', 'Text')
				),
				'subtext' => self::$faq->get('menu_position_sub', 'Text')
			),
			array(
				'check',
				'faqmod_use_javascript',
				'subtext' => self::$faq->get('use_javascript_sub', 'Text')
			),
			array(
				'check',
				'faqmod_show_all',
				'subtext' => self::$faq->get('show_all', 'Text')
			),
			array(
				'check',
				'faqmod_search_engines',
				'subtext' => self::$faq->get('search_engines', 'Text')
			),
			array(
				'check',
				'faqmod_sidebar_side',
				'subtext' => self::$faq->get('sidebar_side', 'Text')
			),
			array(
				'int',
				'faqmod_sidebar_size',
				'subtext' => self::$faq->get('sidebar_size', 'Text')
			),
			array(
				'check',
				'faqmod_care',
				'subtext' => self::$faq->get('care_sub', 'Text')
			),
		);

		$context['post_url'] = $scripturl . '?action=admin;area=faqdmin;sa=basic;save';

		/* Saving? */
		if (isset($_GET['save']))
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=faqdmin;sa=basic');
		}

		prepareDBSettingContext($config_vars);
	}

	/* A whole function for a redirect... yep, sounds about right :P */
	static function EditFaqAdminPage()
	{
		redirectexit('action=faq;sa=manage');
	}

	/* Again?  this is MADNESS! */
	static function AddFaqAdminPage()
	{
		redirectexit('action=faq;sa=add');
	}

	/* Main function */
	static function FaqMain()
	{
		global $context, $scripturl, $modSettings;

		self::Load(array('Subs-Faq', 'Subs-Editor'));
		self::Essential();
		writeLog(true);

		/* Do the permission check, you might not be allowed here. */
		isAllowedTo('faqperview');

		/* Echo the javascript and css bits. */
		self::Headers();

		if (!isset($context['FAQ']['AllCategories']))
			$context['FAQ']['AllCategories'] = self::AllCategories();

		if (!isset($context['FAQ']['AllFaqs']))
			$context['FAQ']['AllFaqs'] = self::AllFaqs();

			$context['faqperview'] = allowedTo('faqperview');
			$context['faqperedit'] = allowedTo('faqperedit');
			$context['FAQ']['copy'] = self::$faq->FaqCare();

		if (!empty($_REQUEST['body_mode']) && isset($_REQUEST['body']))
		{
			$_REQUEST['body'] = html_to_bbc($_REQUEST['body']);
			$_REQUEST['body'] = un_htmlspecialchars($_REQUEST['body']);
			$_POST['body'] = $_REQUEST['body'];
		}

		$subActions = array(
			'manage' => 'WrapperFaqManage',
			'edit' => 'WrapperFaqEdit',
			'edit2' => 'WrapperFaqEdit2',
			'delete' => 'WrapperFaqDelete',
			'delete2' => 'WrapperFaqDelete2',
			'add' => 'WrapperFaqAdd',
			'add2' => 'WrapperFaqAdd2',
			'managecat' => 'WrapperFaqManageCat',
			'editcat' => 'WrapperFaqEditCat',
			'editcat2' => 'WrapperFaqEditCat2',
			'deletecat' => 'WrapperFaqDeleteCat',
			'deletecat2' => 'WrapperFaqDeleteCat2',
			'addcat' => 'WrapperFaqAddCat',
			'addcat2' => 'WrapperFaqAddCat2',
			'category' => 'WrapperFaqCategory',
			'show' => 'WrapperFaqShow',
		);

		// Default to no sub action if nothing was provided or we don't know what they want ;)
		$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : '';

		// Call the right subaction, assuming there is one.
		if (!empty($_REQUEST['sa']))
			$subActions[$_REQUEST['sa']]();

		else
		{
			self::$faq->ShowFaqs();

			$context['page_title'] =  self::$faq->get('title_main', 'Text');
			$context['linktree'][] = array(
				'url' => $scripturl . '?action=faq',
				'name' => self::$faq->get('title_main', 'Text')
			);
			$context['canonical_url'] = $scripturl . '?action=faq';
			$context['sub_template'] = 'faqmod_main';

			/* Does the admin do not want her/his Faq page to be indexed by search engines? */
			if(!empty($modSettings['faqmod_search_engines']))
				$context['robot_no_index'] = true;
		}

		if (empty($context['preview_message']))
		{
			/* Needed for the WYSIWYG editor, we all love the WYSIWYG editor... */
			$modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);

			$editorOptions = array(
				'id' => 'body',
				'value' => !empty($context['edit']['current']['body']) ? $context['edit']['current']['body'] : '',
				'width' => '90%',
			);

			create_control_richedit($editorOptions);
			$context['post_box_name'] = $editorOptions['id'];
		}
	}

	/* Manage the FAQs, show a nice table please... */
	static function FaqManage()
	{
		global $context, $scripturl;

		self::Essential();

		isAllowedTo('faqperedit');

		if (!isset($context['FAQ']['AllFaqs']))
			$context['FAQ']['AllFaqs'] = self::AllFaqs();

		$context['sub_template'] = 'faqmod_show_edit';
		$context['page_title'] = self::$faq->get('manage', 'Text');
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=faq;sa=manage',
			'name' => self::$faq->get('manage', 'Text')
		);
	}

	/* Editing, get the info and show a nice editing form */
	static function FaqEdit()
	{
		global $context, $scripturl;

		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllFaqs']))
			$context['FAQ']['AllFaqs'] = self::AllFaqs();

		if (isset($_REQUEST['faqid']) && in_array($_REQUEST['faqid'], array_keys($context['FAQ']['AllFaqs'])))
		{
			$context['edit']['current'] = $context['FAQ']['AllFaqs'][$_GET['faqid']];
			$context['sub_template'] = 'faqmod_add';
			$context['page_title'] = self::$faq->get('editing', 'Text'). ' - '. $context['edit']['current']['title'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=faq;sa=edit;faqid='.$_GET['faqid'] ,
				'name' => self::$faq->get('edit', 'Text') .' '. $context['edit']['current']['title'],
			);
		}
	}

	/* Got the data?  let's update the FAQ ^o^ */
	function FaqEdit2()
	{
		global $context;

		checkSession('post', '', true);
		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllFaqs']))
			$context['FAQ']['AllFaqs'] = self::AllFaqs();

		if (isset($_GET['faqid']) && in_array($_GET['faqid'], array_keys($context['FAQ']['AllFaqs'])))
		{
			global $txt;

			/* Want to see your masterpiece before others? */
			if (isset($_REQUEST['preview'])){

				$context['edit']['current'] = $context['FAQ']['AllFaqs'][$_GET['faqid']];

				/* Set everything up to be displayed. */
				$context['preview_subject'] = self::$faq->FaqClean($_REQUEST['title']);
				$context['preview_message'] = self::$faq->FaqClean($_REQUEST['body'], true);

				/* Parse out the BBC if it is enabled. */
				$context['preview_message'] = parse_bbc($context['preview_message']);

				/* We Censor for your protection... */
				censorText($context['preview_subject']);
				censorText($context['preview_message']);

				/* Set a descriptive title. */
				$context['page_title'] = $txt['preview'] .' - ' . $context['preview_subject'];

				// Back to the form we go
				self::FaqPreview('faqmod_add');
			}

			else
			{
				self::$faq->editFaq2($_GET['faqid'], $context['user']['id']);
				redirectexit('action=faq;sa=manage');
			}
		}
	}

	/* Lets ask the user if (s)he really want to do this... */
	static function FaqDelete()
	{
		global $context, $scripturl;

		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllFaqs']))
			$context['FAQ']['AllFaqs'] = self::AllFaqs();

		if (isset($_GET['faqid']) && in_array($_GET['faqid'], array_keys($context['FAQ']['AllFaqs'])))
		{
			$context['delete']['current'] = $context['FAQ']['AllFaqs'][$_GET['faqid']];
			$context['sub_template'] = 'faqmod_delete';
			$context['page_title'] = self::$faq->get('deleting', 'Text') .' - '. $context['delete']['current']['title'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=faq;sa=edit;faqid='.$_GET['faqid'] ,
				'name' => self::$faq->get('deleting', 'Text') .' '. $context['delete']['current']['title'],
			);
		}
	}

	// Deleting...
	function FaqDelete2()
	{
		checkSession('post', '', true);
		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllFaqs']))
			$context['FAQ']['AllFaqs'] = self::AllFaqs();

		if (isset($_GET['faqid']) && in_array($_GET['faqid'], array_keys($context['FAQ']['AllFaqs'])))
		{
			self::$faq->delete2($_GET['faqid']);
			redirectexit('action=faq;sa=manage');
		}
	}

	/* Fill out the form to get a nice brand new FAQ... */
	static function FaqAdd()
	{
		global $context, $scripturl;

		isAllowedTo('faqperedit');
		self::Essential();

		$context['sub_template'] = 'faqmod_add';
		$context['page_title'] = self::$faq->get('adding', 'Text');
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=faq;sa=add',
			'name' => self::$faq->get('adding', 'Text'),
		);
	}

	/* Adding... */
	static function FaqAdd2()
	{
		global $context, $scripturl;

		checkSession('post', '', true);
		isAllowedTo('faqperedit');
		self::Essential();

		/* Want to see your masterpiece before others? */
		if (isset($_REQUEST['preview']))
		{
			global $txt;

			/* Set everything up to be displayed. */
			$context['preview_subject'] = self::$faq->FaqClean($_REQUEST['title']);
			$context['preview_message'] = self::$faq->FaqClean($_REQUEST['body'], true);

			/* Parse out the BBC if it is enabled. */
			$context['preview_message'] = parse_bbc($context['preview_message']);

			/* We Censor for your protection... */
			censorText($context['preview_subject']);
			censorText($context['preview_message']);

			/* Set a descriptive title. */
			$context['page_title'] = $txt['preview'] . ' - ' . $context['preview_subject'];

			/* Back to the form we go */
			self::FaqPreview('faqmod_add');
		}

		else
		{
			self::$faq->add2($context['user']['id']);
			redirectexit('action=faq;sa=manage');
		}
	}

	/* Manage the categoriess, show a nice table ...again */
	static function FaqManageCat()
	{
		global $context, $scripturl;

		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllCategories']))
			$context['FAQ']['AllCategories'] = self::AllCategories();

		$context['sub_template'] = 'faqmod_show_edit_cat';
		$context['page_title'] = self::$faq->get('manage_category', 'Text');
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=faq;sa=managecat',
			'name' => self::$faq->get('manage_category', 'Text'),
		);
	}

	/* Editing, get the info and show a nice editing form */
	static function FaqEditCat()
	{
		global $context, $scripturl;

		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllCategories']))
			$context['FAQ']['AllCategories'] = self::AllCategories();

		if (isset($_REQUEST['catid']) && in_array($_REQUEST['catid'], array_keys($context['FAQ']['AllCategories'])))
		{
			$context['editcat']['current'] = $context['FAQ']['AllCategories'][$_GET['catid']];
			$context['sub_template'] = 'faqmod_addcat';
			$context['page_title'] = self::$faq->get('editing_cat', 'Text') .' - '. $context['editcat']['current']['category_name'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=faq;sa=edit;catid='.$_GET['catid'] ,
				'name' => self::$faq->get('edit', 'Text') .' '. $context['editcat']['current']['category_name'],
			);
		}
	}

	/* So, it appears the user has finished with the edits already, fair enough, give her/him some kudos... */
	static function FaqEditCat2()
	{
		global $context;

		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllCategories']))
			$context['FAQ']['AllCategories'] = self::AllCategories();

		if (isset($_GET['catid']) && in_array($_GET['catid'], array_keys($context['FAQ']['AllCategories'])))
		{
			self::$faq->editCat2($_GET['catid'], $context['user']['id']);
			redirectexit('action=faq;sa=managecat');
		}
	}

	/* This time the user will try to delete a category, lets see if (s)he succeeded... */
	static function FaqDeleteCat()
	{
		global $context;

		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllCategories']))
			$context['FAQ']['AllCategories'] = self::AllCategories();

		if (isset($_GET['catid']) && in_array($_GET['catid'], array_keys($context['FAQ']['AllCategories'])))
		{
			$context['deletecat']['current'] = $context['FAQ']['AllCategories'][$_GET['catid']];
			$context['sub_template'] = 'faqmod_delete';  // No point in having yet another subtemplate for this.
			$context['page_title'] = self::$faq->get('deleting', 'Text') .' - '. $context['deletecat']['current']['category_name'];
		}
	}

	/* OMG!  (s)he did it!! */
	static function FaqDeleteCat2()
	{
		checkSession('post', '', true);
		isAllowedTo('faqperedit');
		self::Essential();

		if (!isset($context['FAQ']['AllCategories']))
			$context['FAQ']['AllCategories'] = self::AllCategories();

		if (isset($_GET['catid']) && in_array($_GET['catid'], array_keys($context['FAQ']['AllCategories'])))
		{
			self::$faq->deleteCat2($_GET['catid']);
			redirectexit('action=faq;sa=managecat');
		}
	}

	/* Fill out the form and you will get yourself a nice brand new category ready to be used... */
	static function FaqAddCat()
	{
		global $context;

		isAllowedTo('faqperedit');
		self::Essential();

		$context['sub_template'] = 'faqmod_addcat';
		$context['page_title'] = self::$faq->get('adding_cat', 'Text');
	}

	/* Adding the category   ...finally! */
	static function FaqAddCat2()
	{
		global $context;

		checkSession('post', '', true);
		isAllowedTo('faqperedit');
		self::Essential();
		self::$faq->addCat2($context['user']['id']);
		redirectexit('action=faq;sa=managecat');
	}

	/* Show the FAQs within a category */
	static function FaqCategory()
	{
		global $context, $scripturl;

		if (isset($_GET['catid']))
		{
			isAllowedTo('faqperview');
			self::Essential();
			$show = self::$faq->GetFaqsbyCat($_GET['catid']);
			$catname = self::$faq->GetCategories();

			$context['show']['category'] = $show;
			$context['sub_template'] = 'faqmod_categoryshow';
			$context['page_title'] = $catname[$_GET['catid']]['category_name'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=faq;sa=category;catid='.$_GET['catid'] ,
				'name' => $catname[$_GET['catid']]['category_name'],
			);

			unset($show);
			unset($catname);
		}
	}

	/* Last but not least, let's show a single FAQ... */
	static function FaqShow()
	{
		global $context, $scripturl;

		isAllowedTo('faqperview');
		self::Essential();

		if (!isset($context['FAQ']['AllFaqs']))
			$context['FAQ']['AllFaqs'] = self::AllFaqs();


		if (isset($_GET['faqid']) && in_array($_GET['faqid'], array_keys($context['FAQ']['AllFaqs'])))
		{
			$context['show']['current'] = $context['FAQ']['AllFaqs'][$_GET['faqid']];
			$context['sub_template'] = 'faqmod_show';
			$context['page_title'] =  $context['show']['current']['title'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=faq;sa=show;faqid='.$_GET['faqid'] ,
				'name' => $context['show']['current']['title'],
			);
		}
	}

	/* All the template headers, css, js, etc */
	static function Headers()
	{
		global $context, $modSettings;

		/* Some variables for the width... */
		if(empty($modSettings['faqmod_sidebar_size'])){
			$faqmod_width = 80;
			$sidebar_width = 18;
		}

		else{

			if($modSettings['faqmod_sidebar_size'] > 80){
				$faqmod_width = 80;
				$sidebar_width = 18;
			}

			elseif($modSettings['faqmod_sidebar_size'] < 18){
				$faqmod_width = 18;
				$sidebar_width = 80;
			}

			else{
				$faqmod_width = $modSettings['faqmod_sidebar_size'];
				$sidebar_width = 98 - $modSettings['faqmod_sidebar_size'];
			}
		}

		// Does the user want to use javascript to show/hide the FAQs?
		if(!empty($modSettings['faqmod_use_javascript']) && $context['current_action'] == 'faq')
			$context['html_headers'] .= '
				<script language="JavaScript"  type="text/javascript">
				<!--
				function toggleDiv(divid){
					if(document.getElementById(divid).style.display == \'none\'){
						document.getElementById(divid).style.display = \'block\';
						document.pageLoading.TCallLabel(\'/\',\'restart_function\');
					}
					else{
						document.getElementById(divid).style.display = \'none\';
					}
				}
				//-->
				</script>';

		// CSS!
		if($context['current_action'] == 'faq')
			$context['html_headers'] .=	'

<style type="text/css">
.faqmod_side {
float: '.(empty($modSettings['faqmod_sidebar_side']) ? 'right' : 'left').';
width: '.$sidebar_width.'%;
max-width:80%;
margin:0px;
padding:3px;
}

.faqs {
float:'.(empty($modSettings['faqmod_sidebar_side']) ? 'left' : 'right').';
width:'.$faqmod_width.'%;
max-width:80%;
}

.faqmod_list ul, .faqmod_categories ul {
list-style-image: none;
list-style-position: outside;
list-style-type: none;
padding-left:10px;
}

.faqmod_warning{
border: 1px solid #cc3344;
color: #000;
background-color: #ffe4e9;
padding: 1em;
margin:2px;
}

</style>';

	}

	static function AllCategories()
	{
		self::Essential();

		$temp = self::$faq->GetCategories();

		if (empty($temp))
			$temp2 = array();

		foreach ($temp as $t => $v)
		{
			/* FUGLY! */
			$temp2[$t]['category_last_user'] = self::$faq->GetUser($temp[$t]['category_last_user']);
			$temp2[$t]['category_id'] = $temp[$t]['category_id'];
			$temp2[$t]['category_name'] = $temp[$t]['category_name'];
		}

		return $temp2;
	}

	static function AllFaqs()
	{
		self::Essential();

		$temp = self::$faq->GetFaqs();

		if (empty($temp))
			$temp2 = array();

		foreach ($temp as $t => $v)
		{
			/* FUGLY! */
			$temp2[$t]['last_user'] = self::$faq->GetUser($temp[$t]['last_user']);
			$temp2[$t]['id'] = $temp[$t]['id'];
			$temp2[$t]['category_id'] = $temp[$t]['category_id'];
			$temp2[$t]['title'] = $temp[$t]['title'];
			$temp2[$t]['body'] = self::$faq->GetClean($temp[$t]['body']);
			$temp2[$t]['timestamp'] = $temp[$t]['timestamp'];
		}

		return $temp2;
	}

	static function FaqPreview($area)
	{
		global $context, $sourcedir, $smcFunc, $scripturl, $txt;

		// Put back the faq title and body in the form
		$context['title'] = isset($_REQUEST['title']) ? $smcFunc['htmlspecialchars']($_REQUEST['title']) : '';
		$context['body'] = isset($_REQUEST['body']) ? str_replace(array('  '), array('&nbsp; '), $smcFunc['htmlspecialchars']($_REQUEST['body'])) : '';

		// Build the link tree....
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=faq;sa=' . $area,
			'name' => $txt['faqmod_adding']
		);

		// We need make sure we have this.
		require_once($sourcedir . '/Subs-Editor.php');

		// Create it...
		$editorOptions = array(
			'id' => 'body',
			'value' => !empty($context['body']) ? $context['body'] : '',
			'width' => '90%',
		);

		create_control_richedit($editorOptions);

		// ... and store the ID again for use in the form
		$context['post_box_name'] = $editorOptions['id'];
		$context['sub_template'] = $area;
	}
}