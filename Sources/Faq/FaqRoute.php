<?php

namespace Faq;

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;
use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\FaqRepository;
use Faq\Services\FaqValidation;

class FaqRoute
{
    protected FaqRequest $request;
    protected FaqConfig $config;

    public function __construct(?FaqRequest $request = null, ?FaqConfig $config = null)
    {
        $this->request = $request ?? new FaqRequest();
        $this->config = $config ?? new FaqConfig();
    }

    public const ACTIONS = [
        FaqController::ACTION,
        CategoryController::ACTION
    ];

    /**
     * sub action => permission(s) required to run it.
     *
     * An array means "any of", matching SMF's isAllowedTo() semantics.
     */
    public const PERMISSIONS_MAP = [
        FaqController::ACTION => [
            FaqController::SUB_ACTION_INDEX => FaqAdmin::PERMISSION_VIEW,
            FaqController::SUB_ACTION_SEARCH => FaqAdmin::PERMISSION_VIEW,
            FaqController::SUB_ACTION_SINGLE => FaqAdmin::PERMISSION_VIEW,
            FaqController::SUB_ACTION_CATEGORY => FaqAdmin::PERMISSION_VIEW,
            FaqController::SUB_ACTION_ADD => FaqAdmin::PERMISSION_ADD,
            FaqController::SUB_ACTION_DELETE => FaqAdmin::PERMISSION_DELETE,
            FaqController::SUB_ACTION_MANAGE => [
                FaqAdmin::PERMISSION_ADD,
                FaqAdmin::PERMISSION_DELETE,
            ],
        ],
        CategoryController::ACTION => [
            CategoryController::SUB_ACTION_MANAGE => [
                FaqAdmin::PERMISSION_ADD,
                FaqAdmin::PERMISSION_DELETE,
            ],
            CategoryController::SUB_ACTION_ADD => FaqAdmin::PERMISSION_ADD,
            CategoryController::SUB_ACTION_DELETE => FaqAdmin::PERMISSION_DELETE,
        ],
    ];

    public function dispatch(array &$actions): void
    {
        $action = $this->request->get('action', '');

        // Bail out before building anything: this hook runs on every forum page.
        if (!is_string($action) || !$this->isActionValid($action)
            || !$this->config->setting(FaqAdmin::SETTINGS_ENABLE)) {
            return;
        }

        $controller = $this->makeController($action);
        $subAction = $this->request->get('sa') ?? $controller->getDefaultSubAction();

        if (!is_string($subAction) || !$controller->isSubActionValid($subAction)) {
            return;
        }

        $this->loadRequiredFiles($action);
        $this->request->history($action);
        $this->checkPermissions($action, $subAction);
        $controller->setSubAction($action, $subAction);
        $actions[$action] = [false, fn () => $controller->{$subAction}()];
    }

    /**
     * The one and only place where controllers (and their collaborators) are built.
     */
    protected function makeController(string $action): FaqController | CategoryController
    {
        $request = $this->request;
        $config = $this->config;
        $translator = new FaqTranslator();
        $validation = new FaqValidation();
        $parser = new FaqParser();
        $categoryRepository = new CategoryRepository(new CategoryEntity(), $config);

        if ($action === Faq::NAME) {
            return new FaqController(
                $request,
                $validation,
                $translator,
                $config,
                $parser,
                new FaqRepository(new FaqEntity(FaqEntity::DEFAULT_VALUES), $config),
                $categoryRepository
            );
        }

        return new CategoryController(
            $request,
            $validation,
            $translator,
            $config,
            $parser,
            $categoryRepository
        );
    }

    protected function isActionValid(string $action): bool
    {
        return !empty($action) && in_array($action, self::ACTIONS, true);
    }

    /**
     * Enforces PERMISSIONS_MAP. Controllers keep their own isAllowedTo() calls,
     * this only makes the failure happen before any work is done.
     */
    protected function checkPermissions(string $action, string $subAction): void
    {
        $permissions = self::PERMISSIONS_MAP[$action][$subAction] ?? [];

        if (empty($permissions)) {
            return;
        }

        isAllowedTo(array_map(
            static fn (string $permission): string => Faq::NAME . '_' . $permission,
            (array) $permissions
        ));
    }

    protected function loadRequiredFiles(string $action): void
    {
        loadLanguage(ucfirst(Faq::NAME));
        loadtemplate(ucfirst($action));
    }
}
