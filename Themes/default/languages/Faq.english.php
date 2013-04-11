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

global $scripturl, $txt;

$txt['faqmod_title_main'] = 'FAQ';
$txt['faq_title_admin'] = 'FAQ mod admin settings';
$txt['faqmod_edit'] = 'Edit';
$txt['faqmod_editing'] = 'Editing';
$txt['faqmod_send'] = 'Send';
$txt['faqmod_title_edit'] = 'Title:';
$txt['faqmod_no_category_name'] = 'Yo must provide a name for this category.';
$txt['faqmod_no_category'] = 'You must select a category.';
$txt['faqmod_no_title'] = 'You must provide a title.';
$txt['faqmod_no_body'] = 'You must provide a body.';
$txt['faqmod_last_edit'] = 'Last edit on: ';
$txt['faqmod_delete'] = 'Delete';
$txt['faqmod_delete_con'] = 'Do you really want to delete this: ';
$txt['faqmod_delete_send'] = 'Delete this';
$txt['faqmod_deleting'] = 'Deleting';
$txt['faqmod_add_send'] = 'Add a new FAQ';
$txt['faqmod_create_send'] = 'Create the FAQ';
$txt['faqmod_createCat_send'] = 'Create Category';
$txt['faqmod_edit_send'] = 'Edit this FAQ';
$txt['faqmod_adding'] = 'Adding a new FAQ';
$txt['faqmod_no_faq'] = 'There are no FAQs to show.';
$txt['faqmod_no_cat_admin'] = 'You need to add a category before you can add a FAQ <br /> <a href="'.$scripturl.'?action=faq;sa=addcat">Add a category</a>';
$txt['faqmod_no_cat'] = 'There are not categories, you must add a category first before you can add FAQs.';
$txt['faqmod_adding_cat'] = 'Adding a new category.';
$txt['faqmod_editing_cat'] = 'Editing category';
$txt['faqmod_addcat_send'] = 'Add a new category';
$txt['faqmod_editcat_send'] = 'Edit this category';
$txt['faqmod_no_cat'] = '<span style="color:red;">Without category</span>';
$txt['faqmod_na'] = 'N/A';

// Settings strings
$txt['faqmod_desc'] = 'This page gathers all possible settings for the FAQ mod';
$txt['faqmod_settings_enable'] = 'Enable the FAQ mod';
$txt['faqmod_settings_enable_sub'] = 'This is the master setting, needs to be enable for the mod to work properly.';
$txt['faqmod_settings_search'] = 'Enable searching on FAQs';
$txt['faqmod_settings_search_sub'] = 'This setting needs to be enable to be able to perform searches, users still need to have the proper permission to be able to search.';
$txt['faqmod_settings_letterlist'] = 'Enable a simple A to Z list';
$txt['faqmod_settings_letterlist_sub'] = 'Will place an horizontal list from A to Z with links to a page containing all possible Faqs that start with that letter.';
$txt['faqmod_basic_settings'] = 'Basic Settings';
$txt['faqmod_edit_page'] = 'Edit the FAQs';
$txt['faqmod_manage'] = 'Manage the FAQs';
$txt['faq_list_view_all'] = 'View all';
$txt['faqmod_manage_desc'] = 'From here you can manage your FAQs, you can edit/delete/add as many as you want, here\'s some descriptions:<br />
-ID:  its the numeric reference for the faqs, used to manage the faqs without to much problems.<br />
-Title: the name for the faq.<br />
-Category: The category where this faq is hosted.<br />
-Log: A link to a separate page with info a bout the FAQ, shows who has edited the FAQ and the last time they did it.<br />';
$txt['faqmod_manage_category'] = 'Manage Categories';
$txt['faqmod_manage_category_desc'] = 'From here you can manage your categories, you can edit/delete/add as many as you want.<br />';
$txt['faqmod_admin_panel'] = 'FAQ mod admin panel';
$txt['faqmod_admin_panel_desc'] = 'This is the main admin panel for the FAQ mod, in here you can easily add/edit/delete FAQs as well as setup the configuration.';
$txt['faqmod_php_version'] = '<br /><span style="color:red;font-size:25px">This mod needs php 5.2+ to work properly, you won\'t be able to use this mod</span>';
$txt['faqmod_num_faqs'] = 'Number of FAQs to show on each page.';
$txt['faqmod_num_faqs_sub'] = 'If you have a lot of FAQs you can set a number here to active the pagination, leave it in blank or at 0 if you do not want to have pagination, otherwise put a number, for example, if you have 10 FAQs and you set this at 5 then you will have two pages, 10/5 = 2.';
$txt['faqmod_sort_method'] = 'Sort the FAQ by:';
$txt['faqmod_sort_method_sub'] = 'Select how the FAQs will be sorted, default is by ID.';
$txt['faqmod_date'] = 'By Last edit';
$txt['faqmod_id'] = 'By ID';
$txt['faqmod_title'] = 'By Title';
$txt['faqmod_body'] = 'By Body';
$txt['faqmod_use_javascript'] = 'Use Javascript to hide the body?';
$txt['faqmod_use_javascript_sub'] = 'If enable, this mod will use javascript to hide the FAQ content, this is useful if you have multiple FAQs and want to save some space, to show the content just click on the FAQ title/question.';
$txt['faqmod_show_all'] = 'Show all the FAQs on a list';
$txt['faqmod_show_all_sub'] = 'If checked, a block below the categories will be showed with all the current FAQs';
$txt['faqmod_menu_position'] = 'Select the position for the FAQ button in the menu';
$txt['faqmod_menu_position_sub'] = 'By default is next to home.';
$txt['faqmod_menu_home'] = 'Next to the Home button';
$txt['faqmod_menu_help'] = 'Next to the Help button';
$txt['faqmod_menu_search'] = 'Next to the Search button';
$txt['faqmod_menu_login'] = 'Next to the Login button';
$txt['faqmod_menu_register'] = 'Next to the Register button';
$txt['faqmod_care'] = 'Show the mod author\'s copyright at the bottom of the FAQ page?';
$txt['faqmod_care_sub'] = 'If checked, the copyright will be displayed only in the FAQ page, at the bottom, this will help the mod author to provide more useful and free mods.';
$txt['faqmod_search_engines'] = 'Do not let search engines index the Faq page';
$txt['faqmod_search_engines_sub'] = 'If checked, this mod will place a metatag to discourage search engines from indexing the FAQ page.';
$txt['faqmod_sidebar_side'] = 'Show the side bar at left?';
$txt['faqmod_sidebar_side_sub'] = 'By default, the side bar is showed at right side, if you check this option it will appear at left side.';
$txt['faqmod_sidebar_size'] = 'Size of the FAQs';
$txt['faqmod_sidebar_size_sub'] = 'The width size in percentage % for the FAQs,  the larger the FAQs the smaller will be the side bar, for example, if you set up a width of 50%  then the side bar will have a width of 48%,  enter just the number.<br /> By default is 80 for the FAQs, 18 for the sidebar.';
$txt['faqmod_categories_list'] = 'FAQs within category ';
$txt['faqmod_searc_results'] = 'Search results for ';

// Error strings
$txt['faq_error_enable'] = 'The FAQ mod is not enable.';
$txt['faqmod_no_valid_id'] = 'This is not a valid action.';
$txt['cannot_faq_view'] = 'I\'m sorry, you aren\'t allowed to see the FAQ page';
$txt['faqmod_no_search_results'] = 'No results were found';

// Success
$txt['faqmod_success_title'] = 'successfully done';
$txt['faqmod_success_no_access'] = 'You can\'t access this page directly';
$txt['faqmod_success_message_title'] = 'Thank you';
$txt['faqmod_success_message_generic'] = '<a href="'. $scripturl .'?action=faq" >Go to the FAQ Index</a>';
$txt['faqmod_success_message_add'] = 'You have successfully added a new FAQ entry';
$txt['faqmod_success_message_addCat'] = 'You have successfully added a new category';
$txt['faqmod_success_message_edit'] = 'You have successfully edited this FAQ entry';
$txt['faqmod_success_message_editCat'] = 'You have successfully edited this category';
$txt['faqmod_success_message_delete'] = 'You have successfully deleted this entry';
$txt['faqmod_success_message_deleteCat'] = 'You have successfully deleted this category';

// Template strings
$txt['faqmod_edit_title'] = 'Title';
$txt['faqmod_edit_id'] = 'ID';
$txt['faqmod_edit_last_edit'] = 'Last Edit';
$txt['faqmod_edit_last_edit_by'] = 'By user';
$txt['faqmod_edit_edit'] = 'Edit';
$txt['faqmod_edit_delete'] = 'Delete';
$txt['faqmod_edit/delete'] = 'Edit | Delete';
$txt['faqmod_edit_category'] = 'Category';
$txt['faqmod_edit_name'] = 'Name';
$txt['faqmod_show_categories'] = 'Categories';
$txt['faqmod_show_faqmod_list'] = 'FAQ list';
$txt['faqmod_edit_log'] = 'Log'; 

// Permissions strings
$txt['permissiongroup_simple_faq_per_simple'] = 'FAQ mod permissions';
$txt['permissiongroup_faq_per_classic'] = 'FAQ mod permissions';
$txt['permissionname_faq_search'] = 'Search the FAQs';
$txt['permissionname_faq_edit'] = 'Edit the FAQs';
$txt['permissionname_faq_view'] = 'View the FAQs';
$txt['permissionname_faq_add'] = 'Add the FAQs';
$txt['permissionname_faq_delete'] = 'Delete the FAQs';

// Who's online strings
$txt['whoall_faq'] = 'Viewing the <a href="'. $scripturl. '?action=faq">FAQ page</a>.';
