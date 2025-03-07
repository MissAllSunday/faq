<?php


use Faq\Entities\CategoryEntity;
use Faq\Faq;

function template_faqCategory_index()
{
    echo 'LOL';
}

function template_faqCategory_add()
{
    global $context, $scripturl, $txt;

    /** @var CategoryEntity $category */
    $category = $context[Faq::NAME]['entity'];

    echo '
    <div class="cat_bar">
        <h3 class="catbg">
            ', $context['page_title'] ,'
        </h3>
    </div>';

    echo '
    <div class="roundframe">
        <form>
            <dl class="settings">
                <dt>
                    <span id="caption_subject">', $txt['faq_form_category_name'] ,'</span>
                </dt>
                <dd>
                    <input 
                        type="text" 
                        name="', CategoryEntity::NAME ,'" 
                        size="55" 
                        tabindex="1" 
                        maxlength="255" 
                        value="', $category->getCategoryName() ,'" />
                </dd>
            </dl>
            <input type="submit" name="save" value="', $txt['save'] ,'" class="button floatright">
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
        </form>
    </div>    
    ';
}