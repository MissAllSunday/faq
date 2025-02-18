<?php

use Faq\Entities\FaqEntity;
use Faq\Faq;
use Faq\FaqAdmin;

function template_faq_index()
{
	global $txt, $context;

    showMessage();

	$entities = $context[Faq::NAME]['entities'];

	if (empty($entities)) {
        echo '
    <div class="cat_bar">
        <h3 class="catbg">
            ', $context['page_title'] ,'
        </h3>
    </div>
    <div class="information">
        ', $txt['faq_no_faq'] ,'
    </div>';

        return;
    }

    foreach($entities as $entity)
        echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="floatleft">', $entity->getTitle() ,'</span>
					<span class="floatright">
						', showActions($entity) ,'
					</span>
				</h3>
			</div>
			<div class="information windowbg">
				<div  id="faq_', $entity->getId() ,'">
				', $entity->getBody() ,'
				</div>
			</div>
			<br />';

    // Pagination.
    if (!empty($context['page_index']))
        echo $context['page_index'];
}

function template_faq_add(): void
{
    global $txt, $context;

    $entity = $context[Faq::NAME]['entity'];

    showMessage();

    if (!empty($context[Faq::NAME]['errors'])) {
        showErrors($context[Faq::NAME]['errors']);
    }

    if (!empty($context[Faq::NAME]['preview'])) {
        showPreview($context[Faq::NAME]['preview']);
    }

    echo '
    <div class="cat_bar">
        <h3 class="catbg">
            ', $context['page_title'] ,'
        </h3>
    </div>

    <div class="roundframe"> 
    <form
        action="#"
        method="post" 
        accept-charset="UTF-8" 
        name="'. Faq::NAME .'" 
        id="'. Faq::NAME .'"
        enctype="multipart/form-data"
        target="_self">
        <dl class="settings">
            <dt>
                <span id="caption_subject">', $txt['faq_form_title'] ,'</span>
            </dt>
            <dd>
                <input 
                    type="text" 
                    name="', FaqEntity::TITLE ,'" 
                    size="55" 
                    tabindex="1" 
                    maxlength="255" 
                    value="', $entity->getTitle() ,'" />
            </dd>
            <dt>
                <span class="smalltext">', $txt['faq_form_category'] ,'</span>
            </dt>
            <dd>
                ', showCategoryField($entity) ,'
            </dd>
        </dl>
        
        <div>
           ', template_control_richedit(FaqEntity::BODY, 'smileyBox_message', 'bbcBox_message') ,'
        </div>
        <br />
        <input type="submit" name="save" value="', $txt['save'] ,'" class="button floatright">
        <input type="submit" name="preview" class="button floatright" value="', $txt['preview'] ,'" />
        <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
    </form>
    </div>';
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
	<div>';

	/* A nice form for adding a new cat */
	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<form action="', $scripturl, '?action=Faq;sa=editCat'. (!empty($context['catID']) ? ';cat='. $context['catID'] : '') .'" method="post" target="_self">
					<dl id="post_header">
						<dt>
							<span id="caption_subject">', $txt['Faq_editcat_send'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="title" size="55" tabindex="1" maxlength="255" value="', (!empty($context['currentCat']['name']) ? $context['currentCat']['name'] : '') ,'" class="input_text" /> <input type="submit" name="send" class="sbtn" value="', $txt['Faq_edit'] ,'" />
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

function showActions(FaqEntity $entity): string
{
    global $txt, $scripturl;

    return implode(' | ', array_map(function($action) use ($txt, $entity, $scripturl) {
        $subAction = $action === 'edit' ? 'add' :  $action;
        $url = $scripturl . '?action=' . Faq::NAME . ';sa='. $subAction .';id='. $entity->getId();

        return allowedTo(Faq::NAME . '_' . $action) ?
            '<a href="'. $url .'" class="you_sure">
                '. $txt['faq_' . $action] .'</a>' :
            '';
    }, [
        FaqAdmin::PERMISSION_DELETE,
        FaqAdmin::PERMISSION_EDIT
    ]));
}

function showCategoryField(FaqEntity $entity): string
{
    global $context, $txt;

    if (empty($context[Faq::NAME]['categories'])) {
        return '
            <div class="Faq_warning">
                '. $txt['faq_no_cat_admin'] .'
            </div>';
    }

    $select = '
        <select name="'. FaqEntity::CAT_ID .'">';

    foreach($context[Faq::NAME]['categories'] as $category) {
        $isSelected = $entity->getCatId() === $category->getCategoryId();

        $select .= '
            <option value="'. $category->id .'" '. ($isSelected ? 'selected="selected"' : '') .'>
            '. $category->getCategoryName() .
        '</option>';
    }

    $select .= '
        </select>';

    return $select;
}

function showPreview(array $preview): void
{
    global $txt;

    if (empty($preview['body'])) {
        return;
    }

    echo '
    <div class="cat_bar">
        <h3 class="catbg">
            ', $txt['preview'] ,' - ', $preview['title'] ,'
        </h3>
    </div>

    <div class="roundframe"> 
        ', $preview['body'] ,'
    </div>
    <br />';
}

function showErrors(string $errors): void
{
    global $txt;

    echo '
        <div class="errorbox" id="errors">
		<dl>
			<dt>
				<strong id="error_serious">', $txt['faq_validation_required'] ,'</strong>
			</dt>';

    foreach (explode(',', $errors) as $errorKey) {
        echo '
			<dd class="error">
				- <strong>', $txt['faq_edit_' . trim($errorKey)] , '</strong>
			</dd>';
    }

    echo '
		</dl>
	</div>';
}

function showMessage(): void
{
    global $txt;

    if (!isset($_SESSION[Faq::NAME])){
        return;
    }

    $message = explode('|', $_SESSION[Faq::NAME]);

    echo '
    <div class="', $message[0] ,'box">
        ', $txt['faq_'. $message[0] .'_' . $message[1]] ,'    
    </div>';

    unset($_SESSION[Faq::NAME]);
}
