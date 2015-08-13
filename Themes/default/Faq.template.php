<?php

/**
 * @package FAQ mod
 * @version 2.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license https://www.mozilla.org/MPL/2.0/
 */


function template_faq_main()
{
	global $txt, $context, $scripturl, $modSettings;

	// The master div.
	echo '
	<div class="mainContent">';

	faq_header();

	// Show a nice message if no FAQs are available.
	if (empty($context['Faq']['all']))
			echo '
		<div class="information">
			', $txt['Faq_no_faq'] ,'
		</div>';

	// There are some, lets show em all.
	else
	{
		// Sidebar.
		faq_sideBar();

		// The main div.
		echo '
		<div class="rightSide">';
			foreach($context['Faq']['all'] as $faq)
				echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="floatleft">', $faq['link'] ,'</span>
					<span class="floatright">
						', $faq['crud']['edit'] ,'
						', $faq['crud']['delete'] ,'
					</span>
				</h3>
			</div>
			<div class="information windowbg">
				<div  id="faq_', $faq['id'] ,'">
				', $faq['body'] ,'
				</div>
			</div>
			<br />';

		echo '
		</div>';

		echo '
		<div class="clear">';

		// Pagination.
		if (!empty($context['page_index']))
			echo $context['page_index'];

		// Button for adding a new entry.
		if (allowedTo('faq_add'))
			echo '
			<div>
				<form action="', $scripturl, '?action=Faq;sa=add" method="post" target="_self">
					<input type="submit" name="send" class="input_text" value="', $txt['Faq_add_send'] ,'" />
				</form>
			</div>';

		echo '
		</div>
	</div>';
	}
}

function template_faq_add()
{
	global $context, $scripturl, $txt;

	faq_header();

	// Sidebar.
	faq_sideBar();

	// The main div.
	echo '
	<div class="floatright nopadding">';

	// Show the preview
	if (!empty($context['preview']))
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['preview']['title'] ,'</h3>
		</div>
		<div class="information">
			', $context['preview']['body'] ,'
		</div>
		<br />';

		echo '
		<form action="', $scripturl, '?action=Faq;sa=add" method="post" target="_self" id="postmodify" class="flow_hidden" onsubmit="submitonce(this);smc_saveEntities(\'postmodify\',\'title\');">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'] ,'
				</h3>
			</div>
			<div class="information">
				<dl id="post_header">';

			// Title.
			echo '
					<dt>
						<span id="caption_subject">', $txt['Faq_title_edit'] ,'</span>
					</dt>
					<dd>
						<input type="text" name="current[title]" size="55" tabindex="1" maxlength="255" value="', (isset($context['current']['title']) ? $context['current']['title'] : '') ,'" class="input_text" id="title"/>
					</dd>';

			// Category select field.
			echo'
					<dt>
						<span id="caption_subject">', $txt['Faq_edit_category'] ,':</span>
					</dt>
					<dd>';

			// Show the category select field.
			if (!empty($context['Faq']['cats']))
			{
				echo '
						<select name="current[cat_id]">';

				foreach($context['Faq']['cats'] as $cats)
					echo '
							<option value="', $cats['id'] ,'" ', (isset($context['current']['cat_id']) && $cats['id'] == $context['current']['cat_id'] ? 'selected="selected"' : '') ,'>', $cats['name'] ,'</option>';

				echo '
						</select>';
			}

			else
				echo '
						<div class="Faq_warning">
							', $context['Faq']['no_cat_admin'] ,'
						</div>';

			echo'
					</dd>
				</dl>';

			echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

			echo '
				<div id="confirm_buttons">
					<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="save" class="sbtn" value="', $txt['Faq_create_send'] ,'" />
					<input type="submit" name="preview" class="sbtn" value="', $txt['preview'] ,'" />
				</div>
			</div>
		</form>';

	echo '
	</div>
	<div class="clear"></div>';
}

function template_faq_single()
{
	global $context;

	faq_header();

	// Sidebar.
	faq_sideBar();

	// The main div.
	echo '
	<div class="floatright nopadding" ', $context['Faq']['width'] ,'>';

	// No direct access.
	if (empty($context['Faq']['single']) || !is_array($context['Faq']['single']))
		echo '
		<div class="information">
			', $txt['Faq_no_valid_id'] ,'
		</div>';

	else
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="floatleft">', $context['Faq']['single']['link'] ,'</span>
				<span class="floatright">
					', $context['Faq']['single']['crud']['edit'] ,'
					', $context['Faq']['single']['crud']['delete'] ,'
				</span>
				<span class="clear" />
			</h3>
		</div>

		<div class="windowbg">
			<div class="content">
				', $context['Faq']['single']['body'] ,'
			</div>
		</div>';

	echo '
	</div>
	<div class="clear"></div>';
}

function template_faq_manage()
{
	global $context, $txt, $scripturl;

	faq_header();

	// Sidebar.
	faq_sideBar();

	// The main div.
	echo '
	<div class="rightSide">';

	template_show_list('faq_manage');

	echo '
	</div>';
}

function template_faq_addCat()
{
	global $context, $scripturl, $txt;

	// The main div.
	echo '
	<div class="floatright nopadding">';

	/* A nice form for adding a new cat */
	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<form action="', $scripturl, '?action=Faq;sa=editCat" method="post" target="_self">
					<dl id="post_header">
						<dt>
							<span id="caption_subject">', $txt['Faq_editcat_send'] ,'</span>
						</dt>
						<dd>
							<input type="hidden" id="catID" name="catID" value="', (!empty($context['currentCat']['id']) ? $context['currentCat']['id'] : '') ,'" />
							<input type="text" name="title" size="55" tabindex="1" maxlength="255" value="', (!empty($context['currentCat']['name']) ? $context['currentCat']['name'] : '') ,'" class="input_text" /> <input type="submit" name="send" class="sbtn" value="', $txt['Faq_editcat_send'] ,'" />
						</dd>
					</dl>
				</form>
			</div>
		</div>
		<span class="clear lowerframe">
			<span></span>
		</span><br />';

	echo '
	</div>
	<div class="clear"></div>';
}

function template_faq_list()
{
	global $txt, $context, $scripturl, $modSettings;

	faq_header();

	// Sidebar.
	faq_sideBar();

	// The main div.
	echo '
	<div class="floatright nopadding">';

	/* No direct access */
	if (empty($context['Faq']['all']) || !is_array($context['Faq']['all']))
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

		foreach($context['Faq']['all'] as $all)
			echo '
						<li>
							', $all['link'] ,'
						</li>';

		echo '
					</ul>';

		echo '
			</div>
			<span class="botslice"><span></span></span>
		</div><br />';

	/* Pagination */
	if(!empty($context['page_index']))
		echo '
		<div style="text-align:center;">', $context['page_index'] ,'</div>';

	echo '
	</div>
	<div class="clear"></div>';
}

function faq_header()
{
	global $txt, $scripturl, $context, $settings, $modSettings;

	// Create a link for managing faq.
	$memberlist_buttons = array(
		'manage' => array('text' => 'Faq_manage', 'image' => 'mlist.gif', 'lang' => true, 'url' => $scripturl . '?action=Faq;sa=manage', 'active'=> false),
		'manageCat' => array('text' => 'Faq_manage_categories', 'image' => 'mlist.gif', 'lang' => true, 'url' => $scripturl . '?action=Faq;sa=manageCat', 'active'=> false),
	);

	// Any message to display?
	if (!empty($context['Faq']['update']))
		foreach ($context['Faq']['update'] as $key => $message)
			echo
		'<div class="', $key ,'box">
			', $message ,'
		</div>';

	echo '
		<div class="pagesection">
			', allowedTo(array('faq_edit', 'faq_delete', 'faq_add')) ? template_button_strip($memberlist_buttons, 'right') : '', '
		</div>';

	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="floatleft">', $txt['Faq_main'] ,'</span>';

	if (allowedTo('faq_search'))
		echo '
				<object id="quick_search">
					<form action="', $scripturl, '?action=Faq;sa=search" method="post" accept-charset="', $context['character_set'], '" class="floatright">
						<span class="generic_icons filter centericon"></span>
						<input type="text" name="l_search_value" value="', $txt['search'] , '" onclick="if (this.value == \'', $txt['search'] , '\') this.value = \'\';" class="input_text" />
						<select name="l_column">
							<option value="body" selected="selected">', $txt['Faq_body'] ,'</option>
							<option value="title">', $txt['Faq_title'] ,'</option>
						</select>
						<input type="submit" name="search_go" id="search_go" value="', $txt['search'] , '" class="button_submit" />
					</form>
				</object>';

	echo '
			</h3>
		</div>';
}

function faq_sideBar()
{
	global $context, $scripturl, $txt, $modSettings;

	echo '
	<div class="leftSide" >';

	// Show a nice category list.
	if (!empty($modSettings['Faq_show_catlist']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['Faq_sidebar_faq_cats'] ,'
			</h3>
		</div>

		<div class="information">
			<div class="content">
				<ul class="reset">';

		foreach($context['Faq']['cats'] as $all)
			echo '
					<li>
						<a href="'. $scripturl .'?action=faq;sa=categories;fid='. $all['id'] .'">'. $all['name'] .'</a>
					</li>';

		echo '
				</ul>
			</div>
		</div>
		<br />';
	}

	/* Latest FAQs, calling a model method from the view? naughty, naughty me! */
	if (!empty($modSettings['Faq_show_latest']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $txt['Faq_latest'] ,'</span>
			</h3>
		</div>

		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				<ul class="reset">';

		foreach($context['Faq']['object']->getLatest($modSettings['Faq_show_latest']) as $all)
			echo '
					<li>
						', $all['link'] ,'
					</li>';

		echo '
				</ul>
			</div>
		</div>
		<br />';
	}

	echo '
	</div>';
}
