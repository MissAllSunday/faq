<?php

/**
 * @package FAQ mod
 * @version 2.0
 * @author Jessica González <missallsunday@simplemachines.org>
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

$txt['faq_title'] = 'faq';
$txt['faq_title_index'] = 'faq Index';
$txt['faq_post_title'] = 'Post a new lyric';
$txt['faq_post_by'] = ' by ';
$txt['faq_static_content_default'] = 'You can change this text in your admin settings';
$txt['faq_static_content'] = 'Set a custom text to be show on the faq main page';
$txt['faq_static_content_desc'] = 'You can use bbc code';
$txt['faq_latest_limit'] = 'How many entries will be showd on the latest faq block';
$txt['faq_latest_limit_desc'] = 'If left empty, it will show the latest 10';
$txt['faq_pag_limit'] = 'The amount of faq to be show when showing all faq.';
$txt['faq_pag_limit_desc'] = 'This controls the pagination section, if left empty it will use the dafault value: 20';
$txt['faq_static_title'] = 'General info';
$txt['faq_preview_edit'] = 'Editing a lyric\'s content';
$txt['faq_preview_add'] = 'Adding a new faq entry';
$txt['faq_no_valid_id'] = 'The faq you specified doesn\'t exists';
$txt['faq_no_faq_with_letter'] = 'There are no titles with this letter';
$txt['faq_admin_desc'] = 'From here you can configure your faq mod';
$txt['faq_enable'] = 'Enable the faq mod';
$txt['faq_enable_desc'] = 'This setting must be enable for the mod to work properly.';
$txt['faq_menu_position'] = 'Set the position for the faq mod';
$txt['faq_menu_position_desc'] = 'It will be placed next to the option you choose';
$txt['faq_no_latest'] = 'There are no faq yet.';
$txt['faq_latest_title'] = 'Latest faq added';
$txt['faq_adding'] = 'Adding';
$txt['faq_success_title'] = 'successfully done';
$txt['faq_success_no_access'] = 'You can\'t access this page directly';
$txt['faq_success_message_title'] = 'Thank you';
$txt['faq_success_message_generic'] = '<a href="'. $scripturl .'?action=faq" >Go to the faq Index</a>';
$txt['faq_success_message_add'] = 'You have successfully added a new faq entry';
$txt['faq_success_message_edit'] = 'You have successfully edited this faq entry';
$txt['faq_success_message_delete'] = 'You have successfully deleted this entry';
$txt['faq_artist_title'] = 'faq by ';
$txt['faq_artist_no_content'] = 'There are no faq for this artist';
$txt['faq_list_title'] = 'faq list';
$txt['faq_list_title_sort_by'] = 'sort by ';
$txt['faq_list_title_sort_by_id'] = 'ID';
$txt['faq_list_title_sort_by_title'] = 'Title';
$txt['faq_list_title_sort_by_artist'] = 'Artist';
$txt['faq_list_title_sort_by_latest'] = 'Latest';
$txt['faq_list_title_sort_by_user'] = 'Created by user';
$txt['faq_list_title_by_letter'] = 'faq by letter ';
$txt['faq_list_view_all'] = 'View all faq';
$txt['faq_list_manage_all'] = 'Manage all faq';
$txt['faq_list_artist_list'] = 'Artist list';
$txt['faq_manage_title'] = 'faq manage page';
$txt['faq_manage_desc'] = 'From here you can manage all the faq, you can edit or delete each individual entry.';
$txt['faq_search_title'] = 'Search faq '; 
$txt['faq_search_button'] = 'Search'; 

/* Form */
$txt['faq_title_edit'] = 'Title:';
$txt['faq_title_artist'] = 'Artist:';
$txt['faq_title_body'] = 'faq:';
$txt['faq_create_new'] = 'Add a new entry';
$txt['faq_add_send'] = 'Send';
$txt['faq_edit'] = 'Edit';
$txt['faq_delete'] = 'Delete';
$txt['faq_editing'] = 'Editing';

/* Error */
$txt['faq_error_no_valid_action'] = 'This isn\'t a valid action';
$txt['faq_error_enable'] = 'The mod must be enable';

/* Permissions strings */
$txt['cannot_faqMod_viewfaq'] = 'I\'m sorry, you are not allowed to view the faq page.';
$txt['cannot_faqMod_addfaq'] = 'I\'m sorry, you are not allowed to add faq.';
$txt['cannot_faqMod_editfaq'] = 'I\'m sorry, you are not allowed to edit faq.';
$txt['cannot_faqMod_deletefaq'] = 'I\'m sorry, you are not allowed to delete faq.';
$txt['permissiongroup_simple_faqMod_per_simple'] = 'faq mod permissions';
$txt['permissiongroup_faqMod_per_classic'] = 'faq mod permissions';
$txt['permissionname_faqMod_viewfaq'] = 'View the faq pages';
$txt['permissionname_faqMod_addfaq'] = 'Add faq entries';
$txt['permissionname_faqMod_editfaq'] = 'Edit faq entries';
$txt['permissionname_faqMod_deletefaq'] = 'Delete faq entries';
