<?php


use Faq\Entities\CategoryEntity;
use Faq\Faq;

function template_faqCategory_add(): void
{
    global $txt, $context;

    $entity = $context[Faq::NAME]['entity'];

    if (!empty($context[Faq::NAME]['errors'])) {
        showErrors($context[Faq::NAME]['errors']);
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
                <span id="caption_subject">', $txt['faq_edit_category_name'] ,'</span>
            </dt>
            <dd>
                <input 
                    type="text" 
                    name="', CategoryEntity::NAME ,'" 
                    size="55" 
                    tabindex="1" 
                    maxlength="255" 
                    value="', $entity->getName() ,'" />
            </dd>
        </dl>
        <br />
        <input type="submit" name="save" value="', $txt['save'] ,'" class="button floatright">
        <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
    </form>
    </div>';
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