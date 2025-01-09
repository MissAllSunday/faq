<?php

namespace Faq\Controllers;

use Faq\Faq as Faq;
use Faq\FaqRequest;
use Faq\FaqUtils;

abstract class BaseController
{
    protected ?FaqRequest $request;
    protected ?string $subAction = null;

    public function __construct(?FaqRequest $request = null)
    {
        $this->request = $request ?? new FaqRequest();
    }

    protected function redirect(string $message = ''): void
    {
        global $scripturl;

        $_SESSION[Faq::NAME] = $message;
        $action = '?action=' . $this->getAction();
        $subAction = $this->subAction ?? (';sa=' . $this->subAction);

        redirectexit($scripturl . '?action=' . $action . $subAction);
    }
    public function setSubAction(string $subAction): void
    {
        global $context, $scripturl, $txt;

        $this->subAction = $subAction;

        //  move somewhere else?
        $actionUrl = '?action=' . $this->getAction();
        $subActionUrl = $this->subAction ?? (';sa=' . $this->subAction);
        $context['sub_action'] = $this->subAction;
        $context['sub_template'] = Faq::NAME . '_' . $this->subAction;
        $context['page_title'] = $txt[Faq::NAME . '_' . $this->subAction . '_title'];
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