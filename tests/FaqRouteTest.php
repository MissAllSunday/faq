<?php

namespace Faq;

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * FaqRoute spy: records every controller the router builds so tests can assert
 * that nothing is instantiated for foreign actions or a disabled mod.
 */
class FaqRouteSpy extends FaqRoute
{
    public array $built = [];

    /** @var FaqController|CategoryController|null */
    public $controllerToReturn = null;

    protected function makeController(string $action): FaqController | CategoryController
    {
        $this->built[] = $action;

        return $this->controllerToReturn;
    }
}

class FaqRouteTest extends TestCase
{
    private Stub $request;
    private Stub $config;
    private array $requestValues = [];

    protected function setUp(): void
    {
        global $modSettings;

        $GLOBALS['faq_test_calls'] = [];
        $modSettings['faq_enable'] = true;

        $this->requestValues = [];
        $this->request = $this->createStub(FaqRequest::class);
        $this->request->method('get')->willReturnCallback(
            fn (string $key, $default = null) => $this->requestValues[$key] ?? $default
        );
        $this->request->method('history')->willReturn(['faq_index']);

        $this->config = $this->createStub(FaqConfig::class);
        $this->config->method('setting')->willReturnCallback(
            fn (string $key, $default = false) => $key === FaqAdmin::SETTINGS_ENABLE ?
                ($GLOBALS['modSettings']['faq_enable'] ?? false) : $default
        );
    }

    private function makeRoute(): FaqRouteSpy
    {
        return new FaqRouteSpy($this->request, $this->config);
    }

    public function testForeignActionBuildsNoController(): void
    {
        $this->requestValues = ['action' => 'profile'];
        $actions = ['profile' => [false, 'ProfileMain']];
        $route = $this->makeRoute();

        $route->dispatch($actions);

        self::assertSame([], $route->built);
        self::assertSame(['profile' => [false, 'ProfileMain']], $actions);
    }

    public function testDisabledModBuildsNoController(): void
    {
        global $modSettings;

        $modSettings['faq_enable'] = false;
        $this->requestValues = ['action' => FaqController::ACTION];
        $actions = [];
        $route = $this->makeRoute();

        $route->dispatch($actions);

        self::assertSame([], $route->built);
        self::assertSame([], $actions);
    }

    public function testMissingActionBuildsNoController(): void
    {
        $this->requestValues = [];
        $actions = [];
        $route = $this->makeRoute();

        $route->dispatch($actions);

        self::assertSame([], $route->built);
        self::assertSame([], $actions);
    }

    public function testInvalidSubActionRegistersNothing(): void
    {
        $this->requestValues = ['action' => FaqController::ACTION, 'sa' => 'nope'];
        $controller = $this->createMock(FaqController::class);
        $controller->method('isSubActionValid')->willReturn(false);
        $controller->expects(self::never())->method('setSubAction');

        $actions = [];
        $route = $this->makeRoute();
        $route->controllerToReturn = $controller;

        $route->dispatch($actions);

        self::assertSame([FaqController::ACTION], $route->built);
        self::assertSame([], $actions);
    }

    public function testValidActionRegistersTheSubAction(): void
    {
        $this->requestValues = ['action' => FaqController::ACTION, 'sa' => FaqController::SUB_ACTION_INDEX];
        $controller = $this->createMock(FaqController::class);
        $controller->method('isSubActionValid')->willReturn(true);
        $controller->expects(self::once())->method('setSubAction')
            ->with(FaqController::ACTION, FaqController::SUB_ACTION_INDEX);
        $controller->expects(self::once())->method('index');

        $actions = [];
        $route = $this->makeRoute();
        $route->controllerToReturn = $controller;

        $route->dispatch($actions);

        self::assertArrayHasKey(FaqController::ACTION, $actions);
        self::assertFalse($actions[FaqController::ACTION][0]);

        // The sub action only runs when SMF calls the registered closure.
        ($actions[FaqController::ACTION][1])();
    }

    public function testDefaultSubActionIsUsedWhenSaIsMissing(): void
    {
        $this->requestValues = ['action' => CategoryController::ACTION];
        $controller = $this->createMock(CategoryController::class);
        $controller->method('getDefaultSubAction')->willReturn(CategoryController::SUB_ACTION_MANAGE);
        $controller->method('isSubActionValid')->willReturn(true);
        $controller->expects(self::once())->method('setSubAction')
            ->with(CategoryController::ACTION, CategoryController::SUB_ACTION_MANAGE);

        $actions = [];
        $route = $this->makeRoute();
        $route->controllerToReturn = $controller;

        $route->dispatch($actions);

        self::assertArrayHasKey(CategoryController::ACTION, $actions);
    }

    public function testPermissionsMapCoversEverySubAction(): void
    {
        $map = FaqRoute::PERMISSIONS_MAP;

        self::assertSame(FaqAdmin::PERMISSION_VIEW, $map[FaqController::ACTION][FaqController::SUB_ACTION_INDEX]);
        self::assertSame(FaqAdmin::PERMISSION_ADD, $map[FaqController::ACTION][FaqController::SUB_ACTION_ADD]);
        self::assertSame(FaqAdmin::PERMISSION_DELETE, $map[FaqController::ACTION][FaqController::SUB_ACTION_DELETE]);
        self::assertSame(
            [FaqAdmin::PERMISSION_ADD, FaqAdmin::PERMISSION_DELETE],
            $map[FaqController::ACTION][FaqController::SUB_ACTION_MANAGE]
        );
        self::assertSame(
            FaqAdmin::PERMISSION_DELETE,
            $map[CategoryController::ACTION][CategoryController::SUB_ACTION_DELETE]
        );

        foreach (FaqController::SUB_ACTIONS as $subAction) {
            self::assertArrayHasKey($subAction, $map[FaqController::ACTION]);
        }

        foreach (CategoryController::SUB_ACTIONS as $subAction) {
            self::assertArrayHasKey($subAction, $map[CategoryController::ACTION]);
        }
    }

    public function testDispatchChecksPermissionsBeforeRegistering(): void
    {
        $this->requestValues = ['action' => FaqController::ACTION, 'sa' => FaqController::SUB_ACTION_DELETE];
        $controller = $this->createStub(FaqController::class);
        $controller->method('isSubActionValid')->willReturn(true);

        $actions = [];
        $route = $this->makeRoute();
        $route->controllerToReturn = $controller;

        $route->dispatch($actions);

        $calls = array_values(array_filter(
            $GLOBALS['faq_test_calls'],
            static fn (array $call): bool => $call['function'] === 'isAllowedTo'
        ));

        self::assertCount(1, $calls);
        self::assertSame(['faq_' . FaqAdmin::PERMISSION_DELETE], $calls[0]['arguments'][0]);
    }
}
