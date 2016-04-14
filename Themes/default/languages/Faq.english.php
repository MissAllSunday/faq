<?php

/**
 * @package FAQ mod
 * @version 2.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license https://www.mozilla.org/MPL/2.0/
 */

global $scripturl, $txt;

$txt['Faq_main'] = 'FAQ';
$txt['Faq_admin'] = 'FAQ mod admin settings';
$txt['Faq_edit'] = 'Edit';
$txt['Faq_crud'] = '<a href="{href}">{title}</a>';
$txt['Faq_editing'] = 'Editing';
$txt['Faq_send'] = 'Send';
$txt['Faq_title_edit'] = 'Title';
$txt['Faq_last_edit'] = 'Last edit on: ';
$txt['Faq_delete'] = '<a href="{href}" class="you_sure">Delete</a>';
$txt['Faq_delete_send'] = 'Delete this';
$txt['Faq_deleting'] = 'Deleting';
$txt['Faq_add_send'] = 'Add a new FAQ';
$txt['Faq_create_send'] = 'Create the FAQ';
$txt['Faq_createCat_send'] = 'Create Category';
$txt['Faq_edit_send'] = 'Edit this FAQ';
$txt['Faq_adding'] = 'Adding a new FAQ';
$txt['Faq_no_faq'] = 'There are no FAQs to show.';
$txt['Faq_no_cat_admin'] = 'You need to <a href="{add_cat_href}">add a category</a> before you can add a FAQ';
$txt['Faq_no_cat'] = 'There are not categories, you must add a category first before you can add FAQs.';
$txt['Faq_adding_cat'] = 'Adding a new category.';
$txt['Faq_editing_cat'] = 'Editing category';
$txt['Faq_addcat_send'] = 'Add a new category';
$txt['Faq_editcat_send'] = 'Edit this category';
$txt['Faq_no_cat'] = '<span style="color:red;">Without category</span>';
$txt['Faq_na'] = 'N/A';
$txt['Faq_sidebar_faq_cats'] = 'Category list';
$txt['Faq_latest'] = 'Latest FAQs';
$txt['Faq_action_add'] = 'Add a new FAQ';
$txt['Faq_action_edit'] = 'Edit this FAQ';
$txt['Faq_action_manage'] = 'Manage the FAQs';
$txt['Faq_action_manageCat'] = 'Manage the FAQ categories';
$txt['Faq_action_'] = '';

// Settings strings
$txt['Faq_desc'] = 'This page gathers all possible settings for the FAQ mod';
$txt['Faq_enable'] = 'Enable the FAQ mod';
$txt['Faq_enable_sub'] = 'This is the master setting, needs to be enable for the mod to work properly.';
$txt['Faq_search'] = 'Enable searching on FAQs';
$txt['Faq_search_sub'] = 'This setting needs to be enable to be able to perform searches, users still need to have the proper permission to be able to search.';
$txt['Faq_basic_settings'] = 'Basic Settings';
$txt['Faq_edit_page'] = 'Edit the FAQs';
$txt['Faq_manage'] = 'Manage the FAQs';
$txt['faq_list_view_all'] = 'View all';
$txt['Faq_manage_desc'] = 'From here you can manage your FAQs, you can edit/delete/add as many as you want, here\'s some descriptions:<br />
-ID:  its the numeric reference for the faqs, used to manage the faqs without to much problems.<br />
-Title: the name for the faq.<br />
-Category: The category where this faq is hosted.<br />
-Log: A link to a separate page with info a bout the FAQ, shows who has edited the FAQ and the last time they did it.<br />';
$txt['Faq_manage_categories'] = 'Manage Categories';
$txt['Faq_manage_category_desc'] = 'From here you can manage your categories, you can edit/delete/add as many as you want.<br />';
$txt['Faq_admin_panel'] = 'FAQ mod admin panel';
$txt['Faq_admin_panel_desc'] = 'This is the main admin panel for the FAQ mod, in here you can easily add/edit/delete FAQs as well as setup the configuration.';
$txt['Faq_php_version'] = '<br /><span style="color:red;font-size:25px">This mod needs php 5.2+ to work properly, you won\'t be able to use this mod</span>';
$txt['Faq_num_faqs'] = 'Number of FAQs to show on each page.';
$txt['Faq_num_faqs_sub'] = 'If you have a lot of FAQs you can set a number here to active the pagination, leave it in blank or at 0 if you do not want to have pagination, otherwise put a number, for example, if you have 10 FAQs and you set this at 5 then you will have two pages, 10/5 = 2.';
$txt['Faq_show_catlist_sub'] = 'It will show a list that contain all possible categories. It will list all regardless of it containing any FAQs or not.';
$txt['Faq_show_catlist'] = 'Show a category list on the sidebar';
$txt['Faq_sort_method'] = 'Sort the FAQ by:';
$txt['Faq_sort_method_sub'] = 'Select how the FAQs will be sorted, default is by ID.';
$txt['Faq_date'] = 'By Last edit';
$txt['Faq_id'] = 'By ID';
$txt['Faq_title'] = 'By Title';
$txt['Faq_body'] = 'By Body';
$txt['Faq_byCat'] = 'By Category';
$txt['Faq_use_preview'] = 'Show a short version of the FAQ in the main page.';
$txt['Faq_use_preview_sub'] = 'If enable, the main FAQ page will only show a fraction of the FAQS and the title will be converted to a link to a page that will display the entire FAQ.';
$txt['Faq_how_many'] = 'How many characters would be displayed before the body gets cut off.';
$txt['Faq_how_many_sub'] = 'The "'. $txt['Faq_use_preview'] .'" setting must be enable for this to work.';
$txt['Faq_show_latest'] = 'Show the latest created FAQs on a list and how many';
$txt['Faq_show_latest_sub'] = 'Leave it in blank for not showing anything, otherwise type the mount of latest FAQs to show';
$txt['Faq_menu_position'] = 'Select the position for the FAQ button in the menu';
$txt['Faq_menu_position_sub'] = 'By default is next to home.';
$txt['Faq_care'] = 'Show the mod author\'s copyright at the bottom of the FAQ page?';
$txt['Faq_care_sub'] = 'If checked, the copyright will be displayed only in the FAQ page, at the bottom, this will help the mod author to provide more useful and free mods.';
$txt['Faq_use_jj'] = 'Use Javascript to hide the body?';
$txt['Faq_use_jj_sub'] = 'If enable, this mod will use javascript to hide the FAQ content, this is useful if you have multiple FAQs and want to save some space, to show the content just click on the FAQ title/question.';
$txt['Faq_search_engines'] = 'Do not let search engines index the Faq page';
$txt['Faq_search_engines_sub'] = 'If checked, this mod will place a metatag to discourage search engines from indexing the FAQ page.';
$txt['Faq_sidebar_side'] = 'Show the side bar at left?';
$txt['Faq_sidebar_side_sub'] = 'By default, the side bar is showed at right side, if you check this option it will appear at left side.';
$txt['Faq_sidebar_size'] = 'Size of the FAQs';
$txt['Faq_sidebar_size_sub'] = 'The width size in percentage % for the FAQs,  the larger the FAQs the smaller will be the side bar, for example, if you set up a width of 50%  then the side bar will have a width of 48%,  enter just the number.<br /> By default is 80 for the FAQs, 18 for the sidebar.';
$txt['Faq_categories_list'] = 'FAQs within category ';
$txt['Faq_searc_results'] = 'Search results for ';

// Error strings
$txt['Faq_error_emtpyFields'] = 'The following fields cannot be left empty: {fields}';
$txt['Faq_error_emtpyAll'] = 'The fields cannot be left empty.';
$txt['Faq_error_enable'] = 'The FAQ mod is not enable.';
$txt['Faq_no_valid_id'] = 'This is not a valid action.';
$txt['Faq_no_search_results'] = 'No results were found';
$txt['Faq_search_disable'] = 'The search feature is disable';
$txt['cannot_faq_main'] = 'I\'m sorry, you aren\'t allowed to see the FAQ page';
$txt['cannot_faq_delete'] = 'I\'m sorry, you aren\'t allowed to delete any FAQs';
$txt['cannot_faq_add'] = 'I\'m sorry, you aren\'t allowed to add new FAQs';
$txt['cannot_faq_edit'] = 'I\'m sorry, you aren\'t allowed to edit any FAQs';
$txt['cannot_faq_search'] = 'I\'m sorry, you aren\'t allowed to search the FAQs';
$txt['Faq_error_add'] = 'There was a problem while creating the entry, please try again.';

// Success
$txt['Faq_success_title'] = 'successfully done';
$txt['Faq_success_no_access'] = 'You can\'t access this page directly';
$txt['Faq_success_title'] = 'Thank you';
$txt['Faq_success_add'] = 'You have successfully added a new FAQ entry';
$txt['Faq_success_addCat'] = 'You have successfully added a new category';
$txt['Faq_success_edit'] = 'You have successfully edited this FAQ entry';
$txt['Faq_success_editCat'] = 'You have successfully edited this category';
$txt['Faq_success_delete'] = 'You have successfully deleted this entry';
$txt['Faq_success_deleteCat'] = 'You have successfully deleted this category';

// Template strings
$txt['Faq_edit_title'] = 'Title';
$txt['Faq_edit_id'] = 'ID';
$txt['Faq_edit_last_edit'] = 'Last Edit';
$txt['Faq_edit_last_edit_by'] = 'By user';
$txt['Faq_edit_edit'] = 'Edit';
$txt['Faq_edit_delete'] = 'Delete';
$txt['Faq_edit/delete'] = 'Edit | Delete';
$txt['Faq_edit_category'] = 'Category';
$txt['Faq_edit_name'] = 'Name';
$txt['Faq_show_categories'] = 'Categories';
$txt['Faq_show_faqmod_list'] = 'FAQ list';
$txt['Faq_edit_log'] = 'Log';

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
