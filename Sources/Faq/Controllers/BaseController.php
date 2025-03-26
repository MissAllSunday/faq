<?php

namespace Faq\Controllers;

use Faq\Faq as Faq;
use Faq\FaqRequest;
use Faq\Repositories\RepositoryInterface;
use Faq\Services\FaqValidation;

abstract class BaseController
{
    protected ?FaqRequest $request;
    protected ?string $subAction = null;
    protected FaqValidation $validation;
    protected RepositoryInterface $repository;
    protected int $id;

    const SUCCESS = 'info';
    const ERROR = 'error';

    public function __construct(?FaqRequest $request = null, FaqValidation $validation = null)
    {
        $this->request = $request ?? new FaqRequest();
        $this->validation = $validation ?? new FaqValidation();
    }

    protected function redirect(string $type = self::ERROR, string $subAction = 'generic'): void
    {
        $action = $this->getAction();
        $history = explode('_', $this->request->history()[0]);
        $_SESSION[Faq::NAME . '_message'] = $type . '_' . $action . '_' . $subAction;
        $sa = !empty($history[1]) ? ';sa=' . $history[1] : '';

        redirectexit('action=' . $history[0] . $sa);
    }

    function showMessage(): string
    {
        global $txt;

        $key = Faq::NAME . '_message';

        if (!isset($_SESSION[$key])) {
            return '';
        }

        $message = explode('_', $_SESSION[$key]);
        $text = $txt[Faq::NAME . '_' . $_SESSION[$key]];
        unset($_SESSION[$key]);

        return '
    <div class="'. $message[0] .'box">
        '. $text .'    
    </div>';
    }

    protected function save(array $data, ?int $id = null): void
    {
        $call = $id ? 'update' : 'insert';

        $this->repository->{$call}($data, $id);

        $this->redirect('info', $call);
    }
    public function setSubAction(string $action, string $subAction): void
    {
        global $context, $scripturl, $txt;

        $id = $this->request->get('id');
        $txtKey = $action === Faq::NAME ? '' : (strtolower(str_replace(Faq::NAME, '', $action)) . '_');
        $this->subAction = $subAction;
        $this->id = $id ?? 0;

        //  move somewhere else?
        $subAction = ($id && $this->subAction === 'add' ? 'update' : $this->subAction);
        $actionUrl = '?action=' . $this->getAction();
        $subActionUrl = ($this->subAction ? (';sa=' . $this->subAction) : '') . ($id ? ';id=' . $id : '');
        $context['sub_action'] = $this->subAction;
        $context['sub_template'] = $action . '_' . $this->subAction;
        $context['page_title'] = $txt[Faq::NAME . '_' . $txtKey . $subAction . '_title'];
        $context['post_url'] = $scripturl . $actionUrl . $subActionUrl . ';save';
    }

    public function isSubActionValid(string $subAction): bool
    {
        return !empty($subAction) && in_array($subAction, $this->getSubActions(), true);
    }

    protected function setTemplateVars(array $vars): void
    {
        global $context;

        if (!isset($context[Faq::NAME])) {
            $context[Faq::NAME] = [];
        }

        $context[Faq::NAME] = array_merge($context[Faq::NAME], ['message' => $this->showMessage()], $vars);
    }

    abstract protected function getSubActions() : array;
    abstract protected function getAction() : string;
    abstract public function getDefaultSubAction():string;
}