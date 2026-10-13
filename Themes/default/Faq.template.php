<?php

use Faq\Controllers\FaqController;
use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\Faq;
use Faq\FaqAdmin;
use Faq\FaqUrl;

function template_faq_index(): void
{
	global $txt, $context, $modSettings;

	// Already censored and BBC parsed by the controller.
	$entities = $context[Faq::NAME]['entities'];

    echo '
<div class="mainContent">';

    showMessage();

    showCustomMessage();

    showSideBar();

    $useJavaScript = $modSettings[Faq::NAME .'_'. FaqAdmin::SETTINGS_USE_JS];

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
        foreach($entities as $entity) {
            $id = $entity['id'];
            $anchorHref = $useJavaScript ? '#faq_' . $id :
                FaqUrl::build(FaqController::ACTION, FaqController::SUB_ACTION_SINGLE, $id);

            echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="floatleft">
					    <a href="'. $anchorHref .'" class="faq_toggle_link" id="link_'. $id .'">
					        '. $entity['title'] .'
                        </a>
                    </span>
					<span class="floatright">
						', showActions($id) ,'
					</span>
				</h3>
			</div>
			<div class="information windowbg">
				<div  id="faq_'. $id .'" class="faq_toggle">
				', $entity['body'] ,'
				</div>
			</div>
			<br />';

        }

        showPagination();
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
        <input type="hidden" name="', $context[FaqController::TOKEN_ACTION . '_token_var'], '" value="', $context[FaqController::TOKEN_ACTION . '_token'], '">
    </form>
    </div>';
}

function showSideBar(): void
{
	global $context, $txt, $modSettings;
    
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
                    <form action="', FaqUrl::build(FaqController::ACTION, FaqController::SUB_ACTION_SEARCH) ,'" 
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
						<a href="'. FaqUrl::build(
                FaqController::ACTION,
                FaqController::SUB_ACTION_CATEGORY,
                $category->getId()
            ) .'">'. $category->getName() .'</a>
					</li>';
        }

		echo '
				</ul>
			</div>
		</div>
		<br />';
	}

	echo '
	</div>';
}

function showActions(int $id): string
{
    global $txt;

    return implode(' | ', array_map(function($action) use ($txt, $id) {
        $action = str_replace('_any', '', $action);
        $textKey = $action === 'add' ? 'edit' :  $action;

        // Delete is a GET link, it needs the session id to pass checkSession('get').
        // Edit links stay plain GET, they are read only.
        $url = FaqUrl::build(
            FaqController::ACTION,
            $action,
            $id,
            $action === FaqController::SUB_ACTION_DELETE
        );

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

    /* @var CategoryEntity $category */
    foreach($context[Faq::NAME]['categories'] as $category) {
        $isSelected = $entity->getCatId() === $category->getId();

        $select .= '
            <option value="'. $category->getId() .'" '. ($isSelected ? 'selected="selected"' : '') .'>
            '. $category->getName() .
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

// Both Faq.template.php and FaqCategory.template.php need this and SMF only loads
// the template matching the current action, so the declaration is guarded to make
// the load order irrelevant.
if (!function_exists('showErrors')) {
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
}

function showMessage(): void
{
    global $context;

    echo $context[Faq::NAME]['message'];
}

function showCustomMessage(): void
{
    global $context;

    // Title and already parsed body, provided by the controller.
    $customMessage = $context[Faq::NAME]['customMessage'] ?? [];

    if (empty($customMessage)) {
        return;
    }

    echo '
    <div class="cat_bar">
        <h3 class="catbg">
            <span class="ie6_header floatleft">', $customMessage['title'] ,'</span>
        </h3>
    </div>
    <div class="information">
        ', $customMessage['body'] ,'
    </div>';
}

function showPagination(): void
{
    global $context;

    if (!empty($context[Faq::NAME]['pagination'])) {
        echo '
        <div class="pagesection">
            <div class="pagelinks floatleft">', $context[Faq::NAME]['pagination'] ,'</div>
        </div>';
    }
}