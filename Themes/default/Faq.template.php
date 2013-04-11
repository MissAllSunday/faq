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


function template_faq_main()
{
	global $txt, $context, $scripturl, $modSettings;

	faq_header();

	/* Sidebar */
	echo '
	<div class="floatleft nopadding">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">sidebar title or something</span>
			</h3>
		</div>

		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				side bar stuff here
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

	/* Show a nice message if no FAQs are avaliable */
	if (empty($context['faq']['all']))
			echo '
		<div class="floatright nopadding" style="width:80%;">
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">', $txt['faqmod_no_faq'] ,'</span>
				</h3>
			</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">
				', $txt['faqmod_no_faq'] ,'
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>';

	/* There are some, lets show em all */
	else
	{
		echo '
		<div class="floatright nopadding" style="width:80%;">';

		foreach($context['faq']['all'] as $faq)
		{
			echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">', $faq['title'] ,'</span>
					<span class="floatright">', $context['faq']['object']->crud($faq['id']) ,'</span>
				</h3>
			</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">
				', $faq['body'] ,'
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<br />';
		}

		echo '
		</div>';
	}

	echo '
		<div class="clear">';

	/* Button for adding a new entry */
	if ($context['faq']['object']->permissions('add') == true)
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action='. faq::$name .';sa=add" method="post" target="_self">
					<input type="submit" name="send" class="input_text" value="', $txt['faqmod_add_send'] ,'" />
				</form>
			</div>';

	echo '
		</div>';
}

function template_faq_add()
{
	global $context, $scripturl, $txt;

	// Show the preview
	if (isset($context['preview_message']))
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['preview_title'], '</h3>
		</div>
		<div class="windowbg">
		<span class="topslice"><span></span></span>
			<div class="content">
				', $context['preview_message'], '
			</div>
		<span class="botslice"><span></span></span>
		</div>
		<br />';

		echo '
		<form action="', $scripturl, '?action='. faq::$name .';sa=add2;', (!empty($context['faq']['edit']) || isset($_REQUEST['previewEdit']) ? 'fid='.  (!empty($context['faq']['edit']['id']) ? $context['faq']['edit']['id'] : $_REQUEST['previewEdit']) .';edit' : ''),'" method="post" target="_self" id="postmodify" class="flow_hidden" onsubmit="submitonce(this);smc_saveEntities(\'postmodify\', [\'title\', \'body\']);" >
			<div class="cat_bar">
				<h3 class="catbg">
					',(!empty($context['faq']['edit']) ?  $txt['faqmod_editing'] .' - '. $context['faq']['edit']['title'] : $txt['faqmod_adding']),'
				</h3>
			</div>
			<span class="clear upperframe">
				<span></span>
			</span>
			<div class="roundframe rfix">
				<div class="innerframe">
					<dl id="post_header">';

			/* Title */
			echo '
						<dt>
							<span id="caption_subject">', $txt['faqmod_title_edit'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="title" size="55" tabindex="1" maxlength="255" value="', isset($context['preview_title']) ? $context['preview_title'] : (!empty($context['faq']['edit']) ? $context['faq']['edit']['title'] : '') ,'" class="input_text" />
						</dd>';

			/* Category select field */
			echo'
						<dt>
							<span id="caption_subject">', $txt['faqmod_edit_category'] ,':</span>
						</dt>
						<dd>';

			/* Show the category select field */
			if (!empty($context['faq']['cats']))
			{
				echo '
							<select name="category_id">';

				foreach($context['faq']['cats'] as $cats)
					echo '
								<option value="', $cats['id'] ,'" ', isset($context['preview_cat']) && $cats['id'] == $context['preview_cat'] ? 'selected="selected"' : (isset($context['faq']['edit']['cat']['id']) && $cats['id'] == $context['faq']['edit']['cat']['id'] ? 'selected="selected"' : '') ,'>', $cats['name'] ,'</option>';

				echo '
							</select>';
			}

			else
				echo '
							<div class="faqmod_warning">
								',$txt['faqmod_no_cat_admin'],'
							</div>';

			echo'
						</dd>
					</dl>';

			if ($context['show_bbc'])
				echo '
						<div id="bbcBox_message"></div>';

			if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
				echo '
						<div id="smileyBox_message"></div>';

			echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

			echo '
						<div id="confirm_buttons">
							<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="submit" name="send" class="sbtn" value="',(!empty($context['faq']['edit']) || !empty($_REQUEST['previewEdit']) ? $txt['faqmod_edit_send'] : $txt['faqmod_create_send']),'" />
							<input type="submit" name="preview" class="sbtn" value="', $txt['preview'], '" />
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />
		</form>';
}

function template_faq_success()
{
	global $txt, $context, $scripturl, $modSettings;

	faq_header();

	/* No direct access */
	if (!empty($context['faq']['pin']))
		echo '
	<div class="nopadding" style="width:98%; text-align:center;">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $txt['faqmod_success_message_title'] ,'</span>
			</h3>
		</div>

		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $context['faq']['message'] ,'<p />
				', $txt['faqmod_success_message_generic'] ,'
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';
}

function template_faq_single()
{
	global $txt, $context, $scripturl, $modSettings;

	faq_header();

	/* No direct access */
	if (empty($context['faq']['single']) || !is_array($context['faq']['single']))
		echo '
		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $txt['faqmod_no_valid_id'] ,'
			</div>
			<span class="botslice"><span></span></span>
		</div>';

	else
		echo '
	<div class="nopadding" style="width:98%;">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $context['faq']['single']['title'] ,'</span>
				<span class="floatright">', $context['faq']['object']->crud($context['faq']['single']['id']) ,'</span>
			</h3>
		</div>

		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $context['faq']['single']['body'] ,'
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';
}

function template_faq_manage()
{
	global $context, $txt, $scripturl;

	echo '<div class="cat_bar">
			<h3 class="catbg">', $txt['faqmod_manage'] ,'</h3>
		</div>
		<div class="windowbg description">
			', $txt['faqmod_manage_desc']  ,'
		</div>';

	/* There are no faq to show */
	if (empty($context['faq']['all']))
		echo '
			<span class="clear upperframe">
				<span></span>
			</span>
			<div class="roundframe rfix">
				<div class="innerframe">
					<div class="content">
						', $txt['faqmod_no_faq'] ,'
					</div>
				</div>
			</div>
			<span class="lowerframe">
				<span></span>
			</span><br />';

	else
	{
		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['faqmod_edit_id']  ,'</th>
						<th scope="col">', $txt['faqmod_edit_title'] ,'</th>
						<th scope="col">', $txt['faqmod_edit_log'] ,'</th>
						<th scope="col">', $txt['faqmod_edit_category']  ,'</th>
						<th scope="col" class="last_th">', $txt['faqmod_edit/delete'] ,'</th>
					</tr>
				</thead>
			<tbody>';

		foreach($context['faq']['all'] as $all)
		{
			echo '
				<tr class="windowbg" style="text-align: center">
					<td>
						', $all['id'] ,'
					</td>
					<td>
						',$all['title'],'
					</td>
					<td>
						log
					</td>
					<td>
						', $all['cat']['link'] ,'
					</td>
					<td>
						', $context['faq']['object']->crud($all['id']) ,'
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table><br />';
	}

	/* Button for adding a new entry */
	if ($context['faq']['object']->permissions('add') == true)
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action='. faq::$name .';sa=add" method="post" target="_self">
					<input type="submit" name="send" class="sbtn" value="', $txt['faqmod_add_send'] ,'" />
				</form>
			</div>';

	/* Pagination */
	if(!empty($context['page_index']))
		echo '<div style="text-align:center;">', $context['page_index'] ,'</div>';
}

function template_faq_addCat()
{
	global $context, $scripturl, $txt;

	/* A nice form for adding a new cat */
	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<form action="', $scripturl, '?action='. faq::$name .';sa=editCat" method="post" target="_self">
					<dl id="post_header">
						<dt>
							<span id="caption_subject">', $txt['faqmod_editcat_send'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="title" size="55" tabindex="1" maxlength="255" value="', $context['faq']['cat']['edit']['name'] ,'" class="input_text" /> <input type="submit" name="send" class="sbtn" value="', $txt['faqmod_editcat_send'] ,'" />
						</dd>
					</dl>
				</form>
			</div>
		</div>
		<span class="clear lowerframe">
			<span></span>
		</span><br />';
}

function template_faq_manageCat()
{
	global $context, $txt, $scripturl;

	echo '<div class="cat_bar">
			<h3 class="catbg">', $txt['faqmod_manage'] ,'</h3>
		</div>
		<div class="windowbg description">
			', $txt['faqmod_manage_category_desc'] ,'
		</div>';

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['faqmod_edit_id']  ,'</th>
						<th scope="col">', $txt['faqmod_edit_name']  ,'</th>
						<th scope="col" class="last_th">', $txt['faqmod_edit/delete'] ,'</th>
					</tr>
				</thead>
			<tbody>';

		foreach($context['faq']['cats']['all'] as $all)
		{
			echo '
				<tr class="windowbg" style="text-align: center">
					<td>
						', $all['id'] ,'
					</td>
					<td>
						',$all['name'],'
					</td>
					<td>
						', $context['faq']['object']->crud($all['id'], 'cat') ,'
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table><br />';

	/* Pagination */
	if(!empty($context['page_index']))
		echo '<div style="text-align:center;">', $context['page_index'] ,'</div>';

	/* A nice form for adding a new cat */
	if ($context['faq']['object']->permissions('add') == true)
		echo '
			<span class="clear upperframe">
				<span></span>
			</span>
			<div class="roundframe rfix">
				<div class="innerframe">
					<form action="', $scripturl, '?action='. faq::$name .';sa=addCat" method="post" target="_self">
						<dl id="post_header">
							<dt>
								<span id="caption_subject">', $txt['faqmod_addcat_send'] ,'</span>
							</dt>
							<dd>
								<input type="text" name="title" size="55" tabindex="1" maxlength="255" value="" class="input_text" /> <input type="submit" name="send" class="sbtn" value="', $txt['faqmod_createCat_send'] ,'" />
							</dd>
						</dl>
					</form>
				</div>
			</div>
			<span class="clear lowerframe">
				<span></span>
			</span><br />';
}

function template_faq_list()
{
	global $txt, $context, $scripturl, $modSettings;

	faq_header();

	/* No direct access */
	if (empty($context['faq']['all']) || !is_array($context['faq']['all']))
		echo '
		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $txt['lyrics_error_no_valid_action'] ,'
			</div>
			<span class="botslice"><span></span></span>
		</div>';

	else
		echo '
	<div class="nopadding" style="width:98%;">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $context['page_title'] ,'</span>
			</h3>
		</div>
		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">';

		/* List */
		echo '
					<ul class="reset">';

		foreach($context['faq']['all'] as $all)
			echo '
						<li>
							<a href="', $scripturl, '?action=faq;sa=single;lid=', $all['id'] ,'">', $all['title'] ,'</a> ', $context['faq']['object']->crud($all['id']) ,'
						</li>';

		echo '
					</ul>';

		echo '
			</div>
			<span class="botslice"><span></span></span>
		</div><br />';

	/* Pagination */
	if(!empty($context['page_index']))
		echo '<div style="text-align:center;">', $context['page_index'] ,'</div>';

	/* End div */
	echo '
	</div>';
}

function faq_header()
{
	global $txt, $scripturl, $context, $settings;

	/* Build the letters links */
	$letter_links = '';
	for ($i = 97; $i < 123; $i++)
		$letter_links .= '<a href="' . $scripturl . '?action='. faq::$name .';sa=list;lidletter=' . chr($i) .'">' . strtoupper(chr($i)) . '</a> ';

	/* Create a link for managing faq */
	if ($context['faq']['object']->permissions(array('edit', 'delete')))
			$memberlist_buttons = array(
			'manage' => array('text' => 'faqmod_manage', 'image' => 'mlist.gif', 'lang' => true, 'url' => $scripturl . '?action='. faq::$name .';sa=manage', 'active'=> false),
			'manageCat' => array('text' => 'faqmod_manage_category', 'image' => 'mlist.gif', 'lang' => true, 'url' => $scripturl . '?action='. faq::$name .';sa=manageCat', 'active'=> false),
		);


	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="floatleft">', $letter_links , '</span>
				<object id="quick_search">
					<form action="', $scripturl, '?action='. faq::$name .';sa=search" method="post" accept-charset="', $context['character_set'], '" class="floatright">
						<img src="', $settings['images_url'] , '/filter.gif" alt="" />
						<input type="text" name="l_search_value" value="', $txt['search'] , '" onclick="if (this.value == \'', $txt['search'] , '\') this.value = \'\';" class="input_text" />
						<select name="l_column">
							<option value="body" selected="selected">', $txt['faqmod_body'] ,'</option>
							<option value="title">', $txt['faqmod_title'] ,'</option>
						</select>
						<input type="submit" name="search_go" id="search_go" value="', $txt['search'] , '" class="button_submit" />
					</form>
				</object>
			</h3>
		</div>
		<div class="pagesection">
			', template_button_strip($memberlist_buttons, 'right'), '
		</div>';
}
