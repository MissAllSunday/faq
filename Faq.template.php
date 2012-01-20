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

	function template_faqmod_main()
	{
		global $txt, $context, $scripturl, $modSettings;

		// load the post variables
		loadLanguage('Post');

		faqmod_sidebar();

		echo '<div class="faqs">';

		// No FAQs ? :(
		if (empty($context['GetFaqs']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['faqmod_no_faq'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else {

			//So... we need to define some variables first...
			$faqmod_javascript = '';
			$faqmod_display = '';

			// Lets show the FAQs...
			foreach($context['GetFaqs'] as $faq){

				if (!empty($modSettings['faqmod_use_javascript'])){
					$faqmod_javascript = 'onmousedown="toggleDiv(\'content'.$faq['id'].'\');"';
					$faqmod_display = 'style="display:none;"';
				}

				echo '
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="ie6_header floatleft">
							<a href="',(!empty($modSettings['faqmod_use_javascript']) ? 'javascript:void(0)' : $scripturl.'?action=faq;sa=show;faqid='.$faq['id']),'" ',$faqmod_javascript,'  title="',$txt['faqmod_last_edit'],'',$faq['timestamp'],'"> ',$faq['title'],'</a>
						</span>';

						if($context['faqperedit'])
							echo '<span style="float:right;">
									<a href="',$scripturl,'?action=faq;sa=edit;faqid=',$faq['id'],'">',$txt['faqmod_edit_edit'],'</a> | <a href="',$scripturl,'?action=faq;sa=delete;faqid=',$faq['id'],'">',$txt['faqmod_edit_delete'],'</a>
								</span>';

					echo '</h3>
				</div>
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content',$faq['id'],'" id="content',$faq['id'],'" ',$faqmod_display,'>
							',$faq['body'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';
			}
		}

		if(!empty($modSettings['faqmod_num_faqs']) && !empty($context['GetFaqs']))
			echo '<div style="text-align:center;">',$context['page_index'],'</div>';

		// Add a  new FAQ
		if($context['faqperedit'])
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action=faq;sa=add" method="post" target="_self">
					<input type="submit" name="send" class="sbtn" value="',$txt['faqmod_add_send'],'" />
				</form>
			</div>';

		if(!empty($modSettings['faqmod_care']))
			echo $context['FAQ']['copy'];

		echo '</div><div class="clear"></div>';
	}

	function template_faqmod_delete()
	{
		global $txt, $context, $scripturl;

		if(empty($context['delete']['current']) && !empty($context['deletecat']['current'])){

			$sub_delete = 'deletecat2';
			$faqid = 'catid='.$context['deletecat']['current']['category_id'];
			$name_delete = $context['deletecat']['current']['category_name'];
		}

		elseif(!empty($context['delete']['current']) && empty($context['deletecat']['current'])){

			$sub_delete = 'delete2';
			$faqid = 'faqid='.$context['delete']['current']['id'];
			$name_delete = $context['delete']['current']['title'];
		}

		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">
						',$txt['faqmod_delete'],'
					</span>
				</h3>
			</div>
			<span class="clear upperframe">
				<span></span>
			</span>
			<div class="roundframe rfix">
				<div class="innerframe">
					<div class="content">
						 ',$txt['faqmod_delete_con'],' ',$name_delete,'
					</div>
					<div id="confirm_buttons">
						<form action="', $scripturl, '?action=faq;sa=',$sub_delete,';',$faqid,'" method="post" target="_self">
							<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="submit" name="send" class="sbtn" value="',$txt['faqmod_delete_send'],'" />
						</form>
					</div>
				</div>
			</div>
			<span class="lowerframe">
				<span></span>
			</span><br />';
	}

	function template_faqmod_add()
	{
		global $context, $scripturl, $txt;

			$faqmod_edit = 0;

			if(!empty($context['edit']['current']))
			{
				$faqmod_edit = 1;
				$faqmod_edit_id = 'edit2;faqid='. $context['edit']['current']['id'];
			}

		// Show the preview of the faq?
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
			<form action="', $scripturl, '?action=faq;sa=',$faqmod_edit == 1 ? $faqmod_edit_id : 'add2','" method="post" target="_self" id="postmodify" class="flow_hidden" onsubmit="submitonce(this);smc_saveEntities(\'postmodify\', [\'title\', \'body\']);" >
				<div class="cat_bar">
					<h3 class="catbg">',($faqmod_edit == 1 ?  $txt['faqmod_editing'] : $txt['faqmod_adding']),'</h3>
				</div>
				<span class="clear upperframe">
						<span></span>
					</span>
					<div class="roundframe rfix">
						<div class="innerframe">
						<dl id="post_header">
							<dt>
								<span id="caption_subject">',$txt['faqmod_title_edit'],'</span>
							</dt>
							<dd>
								<input type="text" name="title" size="55" tabindex="1" maxlength="55" value="',$faqmod_edit == 1 ? (isset($context['preview_subject']) ? $context['preview_subject'] : $context['edit']['current']['title']) : (isset($context['preview_subject']) ? $context['preview_subject'] : ''),'" class="input_text" />
							</dd>
							<dt>
								<span id="caption_category">',$txt['faqmod_edit_category'],'</span>
							</dt>
							<dd>';

							if($context['FAQ']['AllCategories'])
							{
								echo'<select name="category_id">';
								foreach($context['FAQ']['AllCategories'] as $cats)
									echo '<option value="', $cats['category_id'] ,'" ', (isset($context['edit']['current']['category_id']) && $cats['category_id'] == $context['edit']['current']['category_id'] ? 'selected="selected"' : '') ,'>', $cats['category_name'] ,'</option>';

									echo '</select>';
							}

							else
								echo '<div class="faqmod_warning">
									',$txt['faqmod_no_cat_admin'],'
									</div>';

						echo'</dd></dl>';

							if ($context['show_bbc'])
								echo '<div id="bbcBox_message"></div>';

							if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
								echo '<div id="smileyBox_message"></div>';

							echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

				echo '
					<div id="confirm_buttons">
						<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="submit" name="send" class="sbtn" value="',($faqmod_edit == 1 ? $txt['faqmod_edit_send'] : $txt['faqmod_add_send']),'" />
						<input type="submit" name="preview" class="sbtn" value="', $txt['preview'], '" />
					</div>
					</div>
					</div>
					<span class="lowerframe">
						<span></span>
					</span><br />
			</form>';
	}

	function template_faqmod_show_edit()
	{
		global $context, $txt, $scripturl;

		echo '<div class="cat_bar">
				<h3 class="catbg">',$txt['faqmod_manage'],'</h3>
			</div>
			<div class="windowbg description">
				',$txt['faqmod_manage_desc'],'
			</div>';

		// No FAQs ? :(
		if (empty($context['FAQ']['AllFaqs']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['faqmod_no_faq'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else{

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">',$txt['faqmod_edit_id'],'</th>
						<th scope="col">',$txt['faqmod_edit_title'],'</th>
						<th scope="col">',$txt['faqmod_edit_category'],'</th>
						<th scope="col">',$txt['faqmod_edit_last_edit'],'</th>
						<th scope="col">',$txt['faqmod_edit_last_edit_by'],'</th>
						<th scope="col">',$txt['faqmod_edit_edit'],'</th>
						<th scope="col" class="last_th">',$txt['faqmod_edit_delete'],'</th>
					</tr>
				</thead>
			<tbody>';

			foreach($context['FAQ']['AllFaqs'] as $faqmod_edit)
			{
				echo '
						<tr class="windowbg" style="text-align: center">
							<td>
							', $faqmod_edit['id'] ,'
							</td>
							<td>
							',$faqmod_edit['title'],'
							</td>
							<td>
							',(!empty($context['FAQ']['AllCategories'][$faqmod_edit['category_id']]['category_name']) ? $context['FAQ']['AllCategories'][$faqmod_edit['category_id']]['category_name'] : $txt['faqmod_no_cat']),'
							</td>
							<td>
							',$faqmod_edit['timestamp'],'
							</td>
							<td>
							',$faqmod_edit['last_user'],'
							</td>
							<td>
							<a href="',$scripturl,'?action=faq;sa=edit;faqid=',$faqmod_edit['id'],'">',$txt['faqmod_edit_edit'],'</a>
							</td>
							<td>
							<a href="',$scripturl,'?action=faq;sa=delete;faqid=',$faqmod_edit['id'],'">',$txt['faqmod_edit_delete'],'</a>
							</td>
						</tr>';
			}

			echo '</tbody>
			</table><br />';
		}

		// Add a  new FAQ
		if($context['faqperedit'])
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action=faq;sa=add" method="post" target="_self">
					<input type="submit" name="send" class="sbtn" value="',$txt['faqmod_add_send'],'" />
				</form>
			</div>';
	}

	function template_faqmod_show_edit_cat()
	{
		global $context, $txt, $scripturl;

		echo '<div class="cat_bar">
				<h3 class="catbg">',$txt['faqmod_manage_category'],'</h3>
			</div>
			<div class="windowbg description">
				',$txt['faqmod_manage_category_desc'],'
			</div>';

		// No Cats ? :(
		if (empty($context['FAQ']['AllCategories']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['faqmod_no_cat_admin'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else{

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">',$txt['faqmod_edit_id'],'</th>
						<th scope="col">',$txt['faqmod_edit_name'],'</th>
						<th scope="col">',$txt['faqmod_edit_last_edit_by'],'</th>
						<th scope="col">',$txt['faqmod_edit_edit'],'</th>
						<th scope="col" class="last_th">',$txt['faqmod_edit_delete'],'</th>
					</tr>
				</thead>
			<tbody>';

			foreach($context['FAQ']['AllCategories'] as $cat_edit)
			{
				echo '
						<tr class="windowbg" style="text-align: center">
							<td>
							',$cat_edit['category_id'],'
							</td>
							<td>
							',$cat_edit['category_name'],'
							</td>
							<td>
							',$cat_edit['category_last_user'],'
							</td>
							<td>
							<a href="',$scripturl,'?action=faq;sa=editcat;catid=',$cat_edit['category_id'],'">',$txt['faqmod_edit_edit'],'</a>
							</td>
							<td>
							<a href="',$scripturl,'?action=faq;sa=deletecat;catid=',$cat_edit['category_id'],'">',$txt['faqmod_edit_delete'],'</a>
							</td>
						</tr>';
			}

			echo '</tbody>
			</table><br />';
		}

		// Add a  new Category
		if($context['faqperedit'])
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action=faq;sa=addcat" method="post" target="_self">
					<input type="submit" name="send" class="sbtn" value="',$txt['faqmod_addcat_send'],'" />
				</form>
			</div>';
	}

	function template_faqmod_addcat()
	{
		global $scripturl, $txt, $context;

		$edit_catid = '';

		if(!empty($context['editcat']['current']))
			$edit_catid = ';catid='.$context['editcat']['current']['category_id'];

				echo '
		<form action="', $scripturl, '?action=faq;sa=',(empty($context['editcat']['current']) ? 'addcat2' : 'editcat2'),'',$edit_catid,'" method="post" target="_self" id="postmodify" class="flow_hidden">
			<div class="cat_bar">
				<h3 class="catbg">',(empty($context['editcat']['current']) ? $txt['faqmod_adding_cat'] : $txt['faqmod_editing_cat']),'</h3>
			</div>
			<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
					<dl id="post_header">
						<dt>
							<span id="caption_category">',$txt['faqmod_edit_name'],'</span>
						</dt>
						<dd>
							<input type="text" name="category_name" size="55" tabindex="1" maxlength="55" value="',(empty($context['editcat']['current']) ? '' : $context['editcat']['current']['category_name']),'" class="input_text" />
						</dd>
					</dl>
				<div id="confirm_buttons">
					<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="send" class="sbtn" value="',(empty($context['editcat']['current']) ? $txt['faqmod_addcat_send'] : $txt['faqmod_editcat_send']),'" />
				</div>
				</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />
		</form>';
	}

	function template_faqmod_show()
	{
		global $txt, $context, $scripturl, $modSettings;

		faqmod_sidebar();

		echo '<div class="faqs">';

		// No FAQs ? :(
		if (empty($context['show']['current']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['faqmod_no_faq'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else {

			//So... we need to define some variables first...
			$faqmod_javascript = '';
			$faqmod_display = '';


				if (!empty($modSettings['faqmod_use_javascript'])){
					$faqmod_javascript = 'onmousedown="toggleDiv(\'content'.$context['show']['current']['id'].'\');"';
					$faqmod_display = 'style="display:none;"';
				}

				echo '
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="ie6_header floatleft">
							<a href="',(!empty($modSettings['faqmod_use_javascript']) ? 'javascript:void(0)' : $scripturl.'?action=faq;sa=show;faqid='.$context['show']['current']['id']),'" ',$faqmod_javascript,'  title="',$txt['faqmod_last_edit'],'',$context['show']['current']['timestamp'],'"> ',$context['show']['current']['title'],'</a>
						</span>';

						if($context['faqperedit'])
							echo '<span style="float:right;">
									<a href="',$scripturl,'?action=faq;sa=edit;faqid=',$context['show']['current']['id'],'">',$txt['faqmod_edit_edit'],'</a> | <a href="',$scripturl,'?action=faq;sa=delete;faqid=',$context['show']['current']['id'],'">',$txt['faqmod_edit_delete'],'</a>
								</span>';

					echo '</h3>
				</div>
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content',$context['show']['current']['id'],'" id="content',$context['show']['current']['id'],'" ',$faqmod_display,'>
							',parse_bbc($context['show']['current']['body']),'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';
		}

		if(!empty($modSettings['faqmod_care']))
			echo $context['FAQ']['copy'];

		echo'</div><div class="clear"></div>';
	}

	function template_faqmod_categoryshow()
	{
		global $txt, $context, $scripturl, $modSettings;

		faqmod_sidebar();

		echo '<div class="faqs">';

		// No FAQs ? :(
		if (empty($context['show']['category']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['faqmod_no_faq'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else {

			//So... we need to define some variables first...
			$faqmod_javascript = '';
			$faqmod_display = '';

			foreach($context['show']['category'] as $faqmod_category){

				if (!empty($modSettings['faqmod_use_javascript'])){
					$faqmod_javascript = 'onmousedown="toggleDiv(\'content'.$faqmod_category['id'].'\');"';
					$faqmod_display = 'style="display:none;"';
				}

				echo '
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="ie6_header floatleft">
							<a href="',(!empty($modSettings['faqmod_use_javascript']) ? 'javascript:void(0)' : $scripturl.'?action=faq;sa=show;faqid='.$faqmod_category['id']),'" ',$faqmod_javascript,'  title="',$txt['faqmod_last_edit'],'',$faqmod_category['timestamp'],'"> ',$faqmod_category['title'],'</a>
						</span>';

						if($context['faqperedit'])
							echo '<span style="float:right;">
									<a href="',$scripturl,'?action=faq;sa=edit;faqid=',$faqmod_category['id'],'">',$txt['faqmod_edit_edit'],'</a> | <a href="',$scripturl,'?action=faq;sa=delete;faqid=',$faqmod_category['id'],'">',$txt['faqmod_edit_delete'],'</a>
								</span>';

					echo '</h3>
				</div>
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content',$faqmod_category['id'],'" id="content',$faqmod_category['id'],'" ',$faqmod_display,'>
							',parse_bbc($faqmod_category['body']),'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';
			}
		}

		if(!empty($modSettings['faqmod_care']))
			echo $context['FAQ']['copy'];

		echo'</div><div class="clear"></div>';
	}

	function faqmod_sidebar()
	{
		global $context, $scripturl, $modSettings, $txt;

		echo '<div class="faqmod_side">
					<div class="cat_bar">
						<h3 class="catbg">
							<span class="ie6_header floatleft">
								',$txt['faqmod_show_categories'],'
							</span></h3>
					</div>';

		if(empty($context['FAQ']['AllCategories']))
			echo '<div class="faqmod_categories faqmod_warning">
					',$txt['faqmod_no_cat'],'
				</div>';

		else{

			echo '<div class="faqmod_categories description"><ul>';

			foreach($context['FAQ']['AllCategories'] as $category)
				echo '<li><a href="',$scripturl,'?action=faq;sa=category;catid=',$category['category_id'],'">',$category['category_name'],'</a></li>';

			echo '</ul>
			</div>';
		}


		//Show a list of all FAQs
		if(!empty($modSettings['faqmod_show_all']) && !empty($context['FAQ']['AllFaqs'])){

			echo '<div class="cat_bar">
						<h3 class="catbg">
							<span class="ie6_header floatleft">
								',$txt['faqmod_show_faqmod_list'],'
							</span></h3>
					</div>
				<div class="faqmod_list description">
					<ul>';

			foreach($context['FAQ']['AllFaqs'] as $faq)
				echo '<li><a href="',$scripturl,'?action=faq;sa=show;faqid=',$faq['id'],'">',$faq['title'],'</a></li>';

			echo '</ul></div>';
		}

		// faqmod_side end
		echo '</div>';
	}