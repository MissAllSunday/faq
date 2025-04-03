<?php

namespace Faq;

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;
use Faq\Faq as Faq;

class FaqRoute
{
    protected ?FaqRequest $request = null;
    protected ?FaqController $faqController;
    protected ?CategoryController $categoryController;
    protected ?FaqUtils $utils;

    // No DI  :(
    public function __construct(
        ?FaqRequest         $request = null,
        ?FaqController      $faqController = null,
        ?CategoryController $categoryController = null,
        ?FaqUtils            $utils = null
    )
    {
        $this->request = $request ?? new FaqRequest();
        $this->faqController = $faqController ?? new FaqController();
        $this->categoryController = $categoryController ?? new CategoryController();
        $this->utils = $utils ?? new FaqUtils();
    }

    public const ACTIONS = [
        FaqController::ACTION,
        CategoryController::ACTION
    ];

    public function dispatch(array &$actions): void
    {
        $action = $this->request->get('action');

        if (!$this->utils->setting(FaqAdmin::SETTINGS_ENABLE) || !$this->isActionValid($action)) {
            return;
        }

        $controller = $this->getController($action);
        $subAction = $this->request->get('sa') ?? $controller->getDefaultSubAction();

        if (!$subAction || !$controller->isSubActionValid($subAction)) {
            return;
        }

        $this->loadRequiredFiles($action);
        $this->request->history($action);
        $controller->setSubAction($action, $subAction);
        $actions[$action] = [false, fn () => $controller->{$subAction}()];
    }

    protected function getController(string $action): FaqController | CategoryController
    {
        return $action === Faq::NAME ? $this->faqController : $this->categoryController;
    }

    protected function isActionValid(string $action): bool
    {
        return !empty($action) && in_array($action, self::ACTIONS, true);
    }

    protected function loadRequiredFiles(string $action): void
    {
        loadLanguage(ucfirst(Faq::NAME));
        loadtemplate(ucfirst($action));
    }
}