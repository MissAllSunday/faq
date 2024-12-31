<?php

namespace Faq;

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;
use Faq\Faq as Faq;

class Route
{
    protected ?Request $request = null;
    protected ?FaqController $faqController;
    protected ?CategoryController $categoryController;

    // No DI  :(
    public function __construct(
        ?Request $request = null,
        ?FaqController $faqController = null,
        ?CategoryController $categoryController = null
    )
    {
        $this->request = $request ?? new Request();
        $this->faqController = $faqController ?? new FaqController();
        $this->categoryController = $categoryController ?? new CategoryController();
    }

    public const ACTIONS = [
        FaqController::ACTION,
        CategoryController::ACTION
    ];

    public function dispatch(array &$actions): void
    {
        $action = $this->request->get('action');

        if (!$this->isActionValid($action)) {
            return;
        }

        $controller = $this->getController($action);
        $subAction = $this->request->get('sa');

        if ($subAction && !$controller->isSubActionValid($subAction)) {
            return;
        }

        $this->loadRequiredFiles();

        $controller->setContext($subAction);
        $actions[$action] = [false, fn () => $controller->{$subAction}()];
    }

    protected function getController(string $action): object
    {
        return $action === Faq::NAME ? $this->faqController : $this->categoryController;
    }

    protected function isActionValid(string $action): bool
    {
        return !empty($action) || in_array($action, self::ACTIONS, true);
    }

    protected function loadRequiredFiles(): void
    {
        \loadLanguage(Faq::NAME);
        \loadtemplate(Faq::NAME);
    }
}