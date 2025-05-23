<?php

/**
 * @package FAQ mod
 * @version 2.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license https://www.mozilla.org/MPL/2.0/
 */

global $scripturl, $txt;

// Actions
$txt['faq_index_title'] = 'Frequently Asked Questions';
$txt['faq_add_title'] = 'Add a new question';
$txt['faq_category_title'] = 'Faq Categories';
$txt['faq_category_add_title'] = 'Add a new category';
$txt['faq_category_update_title'] = 'Edit category';
$txt['faq_update_title'] = 'Edit question';
$txt['faq_category_delete_title'] = 'Delete category';

// Categories
$txt['faq_category_index_title'] = 'Manage Categories';


//Add form
$txt['faq_form_category'] = 'Category';
$txt['faq_form_title'] = 'Title';
$txt['faq_edit_category_name'] = 'Name';
$txt['faq_edit_title'] = 'Title';
$txt['faq_edit_body'] = 'Body';

// Validation errors
$txt['faq_validation_required'] = 'The following fields are required:';
$txt['faq_validation_type'] = 'Invalid type provided.';


$txt['faq_title_main'] = 'FAQ';
$txt['faq_title_admin'] = 'FAQ mod admin settings';
$txt['faq_edit'] = 'Edit';
$txt['faq_editing'] = 'Editing';
$txt['faq_send'] = 'Send';

$txt['faq_no_category_name'] = 'Yo must provide a name for this category.';
$txt['faq_no_category'] = 'You must select a category.';
$txt['faq_no_title'] = 'You must provide a title.';
$txt['faq_no_body'] = 'You must provide a body.';
$txt['faq_last_edit'] = 'Last edit on: ';
$txt['faq_delete'] = 'Delete';
$txt['faq_delete_con'] = 'Do you really want to delete this: ';
$txt['faq_delete_send'] = 'Delete this';
$txt['faq_deleting'] = 'Deleting';
$txt['faq_add_send'] = 'Add a new FAQ';
$txt['faq_create_send'] = 'Create the FAQ';
$txt['faq_createCat_send'] = 'Create CategoryController';
$txt['faq_edit_send'] = 'Edit this FAQ';
$txt['faq_adding'] = 'Adding a new FAQ';
$txt['faq_no_faq'] = 'There are no FAQs to show.';
$txt['faq_no_cat_admin'] = 'You need to <a href="{add_cat_href}">add a category</a> before you can add a FAQ';
$txt['faq_no_cat'] = 'There are not categories, you must add a category first before you can add FAQs.';
$txt['faq_adding_cat'] = 'Adding a new category.';
$txt['faq_editing_cat'] = 'Editing category';
$txt['faq_editcat_send'] = 'Edit this category';
$txt['faq_no_cat'] = '<span style="color:red;">Without category</span>';
$txt['faq_na'] = 'N/A';
$txt['faq_sidebar_faq_cats'] = 'Categories';
$txt['faq_you_sure'] = 'Are you sure you want to delete?';
$txt['faq_latest'] = 'Latest FAQs';

// Settings strings
$txt['faq_desc'] = 'This page gathers all possible settings for the FAQ mod';
$txt['faq_enable'] = 'Enable the FAQ mod';
$txt['faq_enable_sub'] = 'This is the master setting, needs to be enable for the mod to work properly.';
$txt['faq_basic_settings'] = 'Basic Settings';
$txt['faq_edit_page'] = 'Edit the FAQs';
$txt['faq_manage_title'] = 'Manage FAQs';
$txt['faq_single_title'] = 'Single FAQ';
$txt['faq_list_view_all'] = 'View all';
$txt['faq_manage_desc'] = 'From here you can manage your FAQs, you can edit/delete/add as many as you want, here\'s some descriptions:<br />
-ID:  its the numeric reference for the faqs, used to manage the faqs without to much problems.<br />
-Title: the name for the faq.<br />
-CategoryController: The category where this faq is hosted.<br />
-Log: A link to a separate page with info a bout the FAQ, shows who has edited the FAQ and the last time they did it.<br />';
$txt['faq_category_manage_title'] = 'Manage Categories';
$txt['faq_manage_category_desc'] = 'From here you can manage your categories, you can edit/delete/add as many as you want.<br />';
$txt['faq_admin_settings'] = 'FAQ settings';
$txt['faq_admin_permissions'] = 'FAQ permissions';
$txt['faq_admin_panel'] = 'FAQ mod admin panel';
$txt['faq_admin_panel_desc'] = 'This is the main admin panel for the FAQ mod, in here you can easily add/edit/delete FAQs as well as setup the configuration.';
$txt['faq_num_faqs'] = 'Number of FAQs to show on each page.';
$txt['faq_num_faqs_sub'] = 'If you have a lot of FAQs you can set a number here to active the pagination, leave it in blank or at 0 if you do not want to have pagination, otherwise put a number, for example, if you have 10 FAQs and you set this at 5 then you will have two pages, 10/5 = 2.';
$txt['faq_show_catlist_sub'] = 'It will show a list that contain all possible categories. It will list all regardless of it containing any FAQs or not.';
$txt['faq_show_catlist'] = 'Show a category list on the sidebar';
$txt['faq_sort_order'] = 'Order the FAQ by:';
$txt['faq_sort_order_sub'] = 'How the FAqs are going to be ordered, default is Ascending.';
$txt['faq_sort_order_asc'] = 'Ascending';
$txt['faq_sort_order_desc'] = 'Descending';
$txt['faq_sort_method'] = 'Sort the FAQ by:';
$txt['faq_sort_method_sub'] = 'Select how the FAQs will be sorted, default is by ID.';
$txt['faq_date'] = 'By Last edit';
$txt['faq_id'] = 'By ID';
$txt['faq_title'] = 'By Title';
$txt['faq_category'] = 'By Category';
$txt['faq_body'] = 'By Body';
$txt['faq_by_category'] = 'FAQs By {categoryName}';
$txt['faq_use_preview'] = 'Show a short version of the FAQ in the main page.';
$txt['faq_use_preview_sub'] = 'If enable, the main FAQ page will only show a fraction of the FAQS and the title will be converted to a link to a page that will display the entire FAQ.';
$txt['faq_how_many'] = 'How many characters would be displayed before the body gets cut off.';
$txt['faq_how_many_sub'] = 'The "'. $txt['faq_use_preview'] .'" setting must be enable for this to work.';
$txt['faq_show_latest'] = 'Show the latest created FAQs on a list and how many';
$txt['faq_show_latest_sub'] = 'Leave it in blank for not showing anything, otherwise type the mount of latest FAQs to show';
$txt['faq_menu_position'] = 'Select the position for the FAQ button in the menu';
$txt['faq_menu_position_sub'] = 'By default is next to home.';
$txt['faq_care'] = 'Show the mod author\'s copyright at the bottom of the FAQ page?';
$txt['faq_care_sub'] = 'If checked, the copyright will be displayed only in the FAQ page, at the bottom, this will help the mod author to provide more useful and free mods.';
$txt['faq_custom_message_title_default'] = 'Frequently Asked Questions - FAQ';
$txt['faq_custom_message_title'] = 'Title for your custom message';
$txt['faq_custom_message_title_sub'] = 'If leave empty it will display: ' . $txt['faq_custom_message_title_default'];
$txt['faq_custom_message'] = 'Custom message to show at the top of the FAQ page';
$txt['faq_custom_message_sub'] = 'This setting is for displaying a custom message at the top of the FAQ page, you can use BBC code. Leave it in blank for not showing anything.';
$txt['faq_use_js'] = 'Use Javascript to hide the body?';
$txt['faq_use_js_sub'] = 'If enable, this mod will use javascript to hide the FAQ content, this is useful if you have multiple FAQs and want to save some space, to show the content just click on the FAQ title/question.';
$txt['faq_categories_list'] = 'FAQs within category ';
$txt['faq_search_title'] = 'Search results for {searchValue}';

// Error strings
$txt['faq_error_enable'] = 'The FAQ mod is not enable.';
$txt['faq_no_valid_id'] = 'This is not a valid action.';
$txt['faq_no_search_results'] = 'No results were found';
$txt['faq_search_disable'] = 'The search feature is disable';
$txt['cannot_faq_view'] = 'I\'m sorry, you aren\'t allowed to see the FAQ page';
$txt['cannot_faq_delete'] = 'I\'m sorry, you aren\'t allowed to delete any FAQs';
$txt['cannot_faq_add'] = 'I\'m sorry, you aren\'t allowed to add new FAQs';
$txt['cannot_faq_edit'] = 'I\'m sorry, you aren\'t allowed to edit any FAQs';
$txt['cannot_faq_search'] = 'I\'m sorry, you aren\'t allowed to search the FAQs';
$txt['faq_error_faq_insert'] = 'There was an error creating the FAQ entry, please try again or contact your administrator';
$txt['faq_error_faq_update'] = 'There was an error updating the FAQ entry, please try again or contact your administrator';
$txt['faq_error_generic'] = 'There was a problem, please try again or contact your administrator';

// Success
$txt['faq_success_title'] = 'successfully done';
$txt['faq_success_no_access'] = 'You can\'t access this page directly';
$txt['faq_success_message_title'] = 'Thank you';
$txt['faq_success_message_generic'] = '<a href="'. $scripturl .'?action=faq" >Go to the FAQ Index</a>';
$txt['faq_info_faq_insert'] = 'You have successfully added a new FAQ entry';
$txt['faq_info_faq_update'] = 'You have successfully updated this FAQ entry';
$txt['faq_info_faqCategory_insert'] = 'You have successfully added a new category';
$txt['faq_info_faqCategory_update'] = 'You have successfully updated this category';
$txt['faq_info_faq_delete'] = 'You have successfully deleted the entry';
$txt['faq_info_faqCategory_delete'] = 'You have successfully deleted the category.';
$txt['faq_error_faqCategory_delete'] = 'There was an error deleting the category entry';
$txt['faq_error_faqCategory_delete_default'] = 'The default category cannot be deleted, try updating it instead.';
$txt['faq_error_faqCategory_generic'] = 'There was an problem with the category entry';

// Permissions strings
$txt['permissiongroup_simple_faq_per_simple'] = 'FAQ mod permissions';
$txt['permissiongroup_faq_per_classic'] = 'FAQ mod permissions';
$txt['permissionname_faq_view'] = 'View FAQs / Categories';
$txt['permissionname_faq_add'] = 'Add|Edit FAQs / Categories';
$txt['permissionname_faq_delete_any'] = 'Delete FAQs / Categories';

// Template strings
$txt['faq_id'] = 'ID';
$txt['faq_last_edit'] = 'Last Edit';
$txt['faq_last_edit_by'] = 'By user';
$txt['faq_edit'] = 'Edit';
$txt['faq_delete'] = 'Delete';
$txt['faq_name'] = 'Name';
$txt['faq_show_categories'] = 'Categories';
$txt['faq_show_list'] = 'FAQ list';
$txt['faq_edit_log'] = 'Log';

// For the sake of completeness, lets add a title entry for deleting an entry...
$txt['faq_delete_title'] = 'Deleting an entry...';

// Who's online strings
$txt['whoall_faq'] = 'Viewing the <a href="'. $scripturl. '?action=faq">FAQ page</a>.';
