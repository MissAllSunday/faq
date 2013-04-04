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
	global $txt, $context, $scripturl, $modSettings, $faqObject;

	faq_header();

	/* Static content */
	echo '
	<div class="floatleft nopadding" style="width:40%;">
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

	//Show 'em
	echo '
		<div class="floatright nopadding" style="width:59%;">
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">faq title</span>
				</h3>
			</div>

			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">';

	if (empty($context['faq']['all']))
		echo $txt['faqmod_no_faq'];

	else
	{
		echo '
					<ul class="reset">';

		foreach($context['faq']['latest'] as $latest)
		{
			echo '
						<li>
							<a href="', $scripturl, '?action='. faq::$name .';sa=single;fid=', $latest['id'] ,'">', $latest['title'] ,'</a>', $txt['faq_post_by'] ,'<a href="', $scripturl, '?action='. faq::$name .';sa=artist;fid=', urlencode($latest['artist']) ,'">', $latest['artist'] ,'</a>  ', $context['faq']['object']->crud($latest['id']) ,'
						</li>';
		}

		echo '
					</ul>';
	}

	echo '
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>';

	echo '
		<div class="clear">';

	/* Button for adding a new entry */
	if ($context['faq']['object']->permissions('add') == true)
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action='. faq::$name .';sa=add" method="post" target="_self">
					<input type="submit" name="send" class="sbtn" value="', $txt['faqmod_add_send'] ,'" />
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
							<h3 class="catbg">', $context['preview_subject'], '</h3>
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
		<form action="', $scripturl, '?action='. faq::$name .';sa=add2;', !empty($context['faq']['edit']) ? 'fid='.  $context['faq']['edit']['id'] .';edit' : '','" method="post" target="_self" id="postmodify" class="flow_hidden" onsubmit="submitonce(this);smc_saveEntities(\'postmodify\', [\'title\', \'body\']);" >
			<div class="cat_bar">
				<h3 class="catbg">',(!empty($context['faq']['edit']) ?  $txt['faq_preview_edit'] .' - '. $context['faq']['edit']['title'] : $txt['faq_preview_add']),'</h3>
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
							<span id="caption_subject">',$txt['faq_title_edit'],'</span>
						</dt>
						<dd>
							<input type="text" name="title" size="55" tabindex="1" maxlength="55" value="', isset($context['preview_subject']) ? $context['preview_subject'] : (!empty($context['faq']['edit']) ? $context['faq']['edit']['title'] : '') ,'" class="input_text" />
						</dd>
						';

		/* Artist */
		echo '
						<dt>
							<span id="caption_subject">',$txt['faq_title_artist'],'</span>
						</dt>
						<dd>
							<input type="text" name="artist" size="55" tabindex="1" maxlength="55" value="', isset($context['preview_artist']) ? $context['preview_artist'] : (!empty($context['faq']['edit']) ? $context['faq']['edit']['artist'] : '') ,'" class="input_text" />
						</dd>';

					echo'
					</dl>';

						if ($context['show_bbc'])
							echo '<div id="bbcBox_message"></div>';

						if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
							echo '<div id="smileyBox_message"></div>';

						echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

			echo '
				<div id="confirm_buttons">
					<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="send" class="sbtn" value="',(!empty($context['faq']['edit']) ? $txt['faq_edit'] : $txt['faq_add_send']),'" />
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
				<span class="ie6_header floatleft">', $txt['faq_success_message_title'] ,'</span>
			</h3>
		</div>

		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $context['faq']['message'] ,'<p />
				', $txt['faq_success_message_generic'] ,'
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';
}

function template_faq_artist()
{
	global $txt, $context, $scripturl, $modSettings;

	faq_header();

	echo '
	<div class="nopadding" style="width:98%; text-align:center;">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $context['page_title'] ,'</span>
			</h3>
		</div>

		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">';

	if (empty($context['faq']['artist']))
		echo $txt['faq_artist_no_content'];

	else
	{
		echo '
				<ul class="reset">';

		foreach($context['faq']['artist'] as $artist)
		{
			echo '
					<li>
						<a href="', $scripturl, '?action='. faq::$name .';sa=single;fid=', urlencode($artist['id']) ,'">', $artist['title'] ,'</a>
					</li>';
		}
	}

	echo '
				</ul>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<br />';
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
				', $txt['faq_error_no_valid_action'] ,'
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

function template_faq_list()
{
	global $txt, $context, $scripturl, $modSettings;

	faq_header();

	/* No direct access */
	if (empty($context['faq']['list']) || !is_array($context['faq']['list']))
		echo '
		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $txt['faq_error_no_valid_action'] ,'
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

		foreach($context['faq']['list'] as $all)
		{
			echo '
						<li>
							<a href="', $scripturl, '?action='. faq::$name .';sa=single;fid=', $all['id'] ,'">', $all['title'] ,'</a>', $txt['faq_post_by'] ,'<a href="', $scripturl, '?action='. faq::$name .';sa=artist;fid=', urlencode($all['artist']) ,'">', $all['artist'] ,'</a> ', $context['faq']['object']->crud($all['id']) ,'
						</li>';
		}

		echo '
					</ul>';

		echo '
			</div>
			<span class="botslice"><span></span></span>
		</div>';

	/* Pagination */
	if(!empty($context['page_index']))
		echo '<div style="text-align:center;">', $context['page_index'] ,'</div>';

	/* End div */
	echo '
	</div>';
}

function template_faq_manage()
{
	global $context, $txt, $scripturl;

	echo '<div class="cat_bar">
			<h3 class="catbg">', $txt['faq_manage_title'] ,'</h3>
		</div>
		<div class="windowbg description">
			', $txt['faq_manage_desc']  ,'
		</div>';

	/* There are no faq to show */
	if (empty($context['faq']['list']))
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
						<th scope="col" class="first_th">', $txt['faq_list_title_sort_by_id'] ,'</th>
						<th scope="col">', $txt['faq_list_title_sort_by_title'] ,'</th>
						<th scope="col">', $txt['faq_list_title_sort_by_artist'] ,'</th>
						<th scope="col">', $txt['faq_list_title_sort_by_user'] ,'</th>
						<th scope="col">', $txt['faq_edit'] ,'</th>
						<th scope="col" class="last_th">', $txt['faq_delete'] ,'</th>
					</tr>
				</thead>
			<tbody>';

			foreach($context['faq']['list'] as $all)
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
								',$all['artist'],'
							</td>
							<td>
								',$all['user']['link'],'
							</td>
							<td>
								', $context['faq']['object']->permissions('edit') == true ? '<a href="'. $scripturl .'?action='. faq::$name .';sa=edit;fid='. $all['id'] .'">'. $txt['faq_edit'] .'</a>' : $txt['faq_edit'] ,'
							</td>
							<td>
								', $context['faq']['object']->permissions('delete') == true ? '<a href="'. $scripturl .'?action='. faq::$name .';sa=delete;fid='. $all['id'] .'">'.  $txt['faq_delete'] .'</a>' : $txt['faq_delete'] ,'
							</td>
						</tr>';
			}

			echo '</tbody>
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

function faq_header()
{
	global $txt, $scripturl, $context, $settings;

	/* Build the letters links */
	$letter_links = '';
	for ($i = 97; $i < 123; $i++)
		$letter_links .= '<a href="' . $scripturl . '?action='. faq::$name .';sa=list;lidletter=' . chr($i) .'">' . strtoupper(chr($i)) . '</a> ';

	$memberlist_buttons = array(
			'view_all' => array('text' => 'faq_list_view_all', 'image' => 'mlist.gif', 'lang' => true, 'url' => $scripturl . '?action='. faq::$name .';sa=list', 'active'=> true),
		);

	/* Create a link for managing faq */
	if ($context['faq']['object']->permissions(array('edit', 'delete')))
		$memberlist_buttons['manage'] =  array('text' => 'faqmod_manage', 'image' => 'mlist.gif', 'lang' => true, 'url' => $scripturl . '?action='. faq::$name .';sa=manage', 'active'=> false);

	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="floatleft">', $letter_links , '</span>
				<object id="quick_search">
					<form action="', $scripturl, '?action='. faq::$name .';sa=search" method="post" accept-charset="', $context['character_set'], '" class="floatright">
						<img src="', $settings['images_url'] , '/filter.gif" alt="" />
						<input type="text" name="l_search_value" value="', $txt['search'] , '" onclick="if (this.value == \'', $txt['search'] , '\') this.value = \'\';" class="input_text" />
						<input type="submit" name="search_go" id="search_go" value="', $txt['search'] , '" class="button_submit" />
					</form>
				</object>
			</h3>
		</div>
		<div class="pagesection">
			', template_button_strip($memberlist_buttons, 'right'), '
		</div>';
}
