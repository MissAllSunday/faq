<?php

namespace Faq\Controllers;

use Faq\Faq as Faq;
use Faq\FaqAdmin;
use Faq\FaqConfig;
use Faq\FaqParser;
use Faq\FaqRequest;
use Faq\FaqTranslator;
use Faq\Repositories\RepositoryInterface;
use Faq\Services\FaqValidation;

abstract class BaseController
{
    protected FaqRequest $request;
    protected ?string $subAction = null;
    protected FaqValidation $validation;
    protected RepositoryInterface $repository;
    protected FaqTranslator $translator;
    protected FaqConfig $config;
    protected FaqParser $parser;
    protected int $id;

    const SUCCESS = 'info';
    const ERROR = 'error';

    /**
     * Controllers are built by FaqRoute::makeController() only for our own actions,
     * so every collaborator is required here: no `= null` + `new` fallbacks.
     */
    public function __construct(
        FaqRequest $request,
        FaqValidation $validation,
        FaqTranslator $translator,
        FaqConfig $config,
        FaqParser $parser
    )
    {
        $this->request = $request;
        $this->validation = $validation;
        $this->translator = $translator;
        $this->config = $config;
        $this->parser = $parser;
    }

    protected function redirect(string $type = self::ERROR, string $subAction = 'generic'): void
    {
        $action = $this->getAction();
        $history = explode('_', $this->request->history()[0]);
        $_SESSION[Faq::NAME . '_message'] = $type . '_' . $action . '_' . $subAction;
        $id = !empty($history[2]) ? ';id=' . $history[2] : '';
        $sa = (!empty($history[1]) && $history[1] !== 'index') ? (';sa=' . $history[1] . $id) : '';

        redirectexit('action=' . $history[0] . $sa);
    }

    function showMessage(): string
    {
        $key = Faq::NAME . '_message';

        if (!isset($_SESSION[$key])) {
            return '';
        }

        $message = explode('_', $_SESSION[$key]);
        $text = $this->translator->text($_SESSION[$key]);
        unset($_SESSION[$key]);

        return '
    <div class="'. $message[0] .'box">
        '. $text .'    
    </div>';
    }

    /**
     * SMF session check (CSRF protection for GET links and POST forms).
     *
     * Kept behind a method so tests only need to mock one place.
     *
     * @param string $type 'get', 'post' or 'request'
     */
    protected function verifySession(string $type = 'post'): void
    {
        checkSession($type);
    }

    /**
     * Validates a single use SMF token, fatals when it is missing or wrong.
     */
    protected function verifyToken(string $action, string $type = 'post'): void
    {
        validateToken($action, $type);
    }

    /**
     * Creates a single use SMF token, the matching hidden fields must be
     * emitted by the template.
     */
    protected function createSessionToken(string $action, string $type = 'post'): void
    {
        createToken($action, $type);
    }

    protected function save(array $data, ?int $id = null): void
    {
        if ($id) {
            $this->repository->update($data, $id);

            $this->redirect('info', 'update');
        }

        $this->repository->insert($data);

        $this->redirect('info', 'insert');
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
        $context['page_title'] = $this->translator->text($txtKey . $subAction . '_title');
        $context['current_url'] = $scripturl . $actionUrl . $subActionUrl;
        $context['linktree'][] = [
            'url' => $scripturl . $actionUrl,
            'name' => $this->translator->text('index_title')];

        if ($this->subAction !== 'index') {
            $context['linktree'][] = [
                'url' => $scripturl . $actionUrl . $subActionUrl,
                'name' => $txt[Faq::NAME . '_' . $txtKey . $subAction . '_title']];
        }

        loadCSSFile('faq.css', [], 'smf_faq');

        if ($this->config->setting(FaqAdmin::SETTINGS_USE_JS)) {
            loadJavascriptFile('faqToggle.js', ['defer' => true]);
        }
    }

    public function isSubActionValid(string $subAction): bool
    {
        return !empty($subAction) && in_array($subAction, $this->getSubActions(), true);
    }

    protected function overrideLinkTree(string $name): void
    {
        global $context, $scripturl;

        $id = $this->request->get('id');
        $actionUrl = '?action=' . $this->getAction();
        $subActionUrl = ($this->subAction ? (';sa=' . $this->subAction) : '') . ($id ? ';id=' . $id : '');

        $context['linktree'][array_key_last($context['linktree'])] = [
            'url' => $scripturl . $actionUrl . $subActionUrl,
            'name' => $name];
    }

    protected function setTemplateVars(array $vars, array $smfContextVars = []): void
    {
        global $context;

        if (!isset($context[Faq::NAME])) {
            // TODO STEP 10: parse in the controller and drop this from $context.
            $context[Faq::NAME] = [
                'parser' => $this->parser
            ];
        }

        if (empty($context[Faq::NAME]['message'])) {
            $context[Faq::NAME]['message'] = $this->showMessage();
        }

        $context[Faq::NAME] = array_merge($context[Faq::NAME], $vars);

        if (!empty($smfContextVars)) {
            $context = array_merge($context, $smfContextVars);
        }
    }

    abstract protected function getSubActions() : array;
    abstract protected function getAction() : string;
    abstract public function getDefaultSubAction():string;
}