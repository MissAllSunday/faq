<?php

namespace Faq\Controllers;

use Faq\Faq as Faq;
use Faq\FaqRequest;
use Faq\Services\FaqValidation;

abstract class BaseController
{
    protected ?FaqRequest $request;
    protected ?string $subAction = null;
    protected FaqValidation $validation;

    public function __construct(?FaqRequest $request = null, FaqValidation $validation = null)
    {
        $this->request = $request ?? new FaqRequest();
        $this->validation = $validation ?? new FaqValidation();
    }

    protected function redirect(string $type, string $message = ''): void
    {
        $_SESSION[Faq::NAME] = $type . '|' . $message;
        $action = '?action=' . $this->getAction();
        $subAction = $this->subAction ? (';sa=' . $this->subAction) : '';

        redirectexit($action . $subAction);
    }
    public function setSubAction(string $subAction): void
    {
        global $context, $scripturl, $txt;

        $this->subAction = $subAction;

        //  move somewhere else?
        $id = $this->request->get('id');
        $subAction = ($id && $this->subAction === 'add' ? 'update' : $this->subAction);
        $actionUrl = '?action=' . $this->getAction();
        $subActionUrl = $this->subAction ?? (';sa=' . $this->subAction);
        $context['sub_action'] = $this->subAction;
        $context['sub_template'] = Faq::NAME . '_' . $this->subAction;
        $context['page_title'] = $txt[Faq::NAME . '_' . $subAction . '_title'];
        $context['post_url'] = $scripturl . '?action=' . $actionUrl . $subActionUrl . ';save';
    }

    public function isSubActionValid(string $subAction): bool
    {
        return !empty($subAction) && in_array($subAction, $this->getSubActions(), true);
    }

    protected function setTemplateVars(array $vars): void
    {
        global $context;

        $context[Faq::NAME] = $vars;
    }

    abstract protected function getSubActions() : array;
    abstract protected function getAction() : string;
    abstract public function getDefaultSubAction():string;
}