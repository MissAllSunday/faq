<?php

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;
use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\Faq;
use Faq\FaqAdmin;

function template_faq_index()
{
	global $txt, $context, $scripturl;

	$entities = $context[Faq::NAME]['entities'];

    echo '
<div class="mainContent">';

    showMessage();

    showSideBar();

    echo '
	<div class="rightSide" >';

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
    }
    else {
        foreach($entities as $entity)
            echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="floatleft">
					    <a href="'. $scripturl . '?action='. Faq::NAME .';sa='. FaqController::SUB_ACTION_SINGLE .
                ';id='. $entity->getId() .'">'. $entity->getName() .'</a>
                    </span>
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

    echo '</div>
</div>';
}

function template_faq_add(): void
{
    global $txt, $context;

    $entity = $context[Faq::NAME]['entity'];

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

function showSideBar(): void
{
	global $context, $scripturl, $txt, $modSettings;
    
	echo '
	<div class="leftSide" >';

    if (allowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW)) {
        echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $txt['search'] ,'</span>
			</h3>
		</div>
		<div class="information">
			<div class="content">
                <object id="quick_search">
                    <form action="', $scripturl ,'?action=', FaqController::ACTION ,';sa=', FaqController::SUB_ACTION_SEARCH ,'" 
                    method="post" accept-charset="UTF-8" class="admin_search">
                        <span class="generic_icons filter centericon"></span>
                        <input 
                            type="text"
                            name="search_value"
                            value=""
                            class="input_text"
                            required
                        />
                        <input 
                            type="submit" 
                            name="search" 
                            id="search"
                            value="', $txt['search'] , '" 
                            class="button_submit" />
                    </form>
                </object>
            </div>
        </div>';
    }

	// Show a nice category list.
	if (!empty($modSettings['faq_show_catlist']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['faq_sidebar_faq_cats'] ,'
			</h3>
		</div>

		<div class="information">
			<div class="content">
				<ul class="reset">';

        /* @var CategoryEntity $category */
		foreach($context[Faq::NAME]['categories'] as $category) {
            echo '
					<li>
						<a href="'. $scripturl .'?action=' . FaqController::ACTION .
                ';sa=' . FaqController::SUB_ACTION_CATEGORY . ';id='. $category->getId() .'">'. $category->getName() .'</a>
					</li>';
        }

		echo '
				</ul>
			</div>
		</div>
		<br />';
	}

	if (!empty($modSettings['faq_show_latest']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $txt[Faq::NAME .'_latest'] ,'</span>
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
        $textKey = $action === 'add' ? 'edit' :  $action;
        $url = $scripturl . '?action=' . Faq::NAME . ';sa='. $action .';id='. $entity->getId();

        return allowedTo(Faq::NAME . '_' . $action) ?
            '<a href="'. $url .'" class="you_sure">
                '. $txt['faq_' . $textKey] .'</a>' :
            '';
    }, [
        FaqAdmin::PERMISSION_DELETE,
        FaqAdmin::PERMISSION_ADD
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
    global $context;

    echo $context[Faq::NAME]['message'];
}
