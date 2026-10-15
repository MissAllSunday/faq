<?php

namespace Faq\Controllers;

use Faq\Entities\CategoryEntity;
use Faq\Faq;
use Faq\FaqAdmin;
use Faq\FaqConfig;
use Faq\FaqParser;
use Faq\FaqRequest;
use Faq\FaqTranslator;
use Faq\Repositories\CategoryRepositoryInterface;
use Faq\Services\FaqValidation;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class CategoryControllerTest extends TestCase
{
    private Stub $request;
    private Stub $validation;
    private Stub $translator;
    private Stub $config;
    private Stub $parser;
    private MockObject $categoryRepository;

    /** @var array<string, mixed> values returned by FaqRequest::get() */
    private array $requestValues = [];
    private bool $isPost = false;
    private array $requestAll = [];
    private array $requestSet = [];

    protected function setUp(): void
    {
        global $context, $txt;

        $GLOBALS['faq_test_calls'] = [];
        $_SESSION = [];
        $context = [
            'current_url' => 'localhost?action=faqCategory',
            'linktree' => [['url' => 'localhost', 'name' => 'Home']],
        ];
        $txt = [];

        $this->requestValues = [];
        $this->isPost = false;
        $this->requestAll = [];
        $this->requestSet = [];

        $this->request = $this->createStub(FaqRequest::class);
        $this->request->method('get')->willReturnCallback(
            fn (string $key, $default = null) => $this->requestValues[$key] ?? $default
        );
        // Callbacks read the property at call time, so tests can flip these after setUp().
        $this->request->method('isPost')->willReturnCallback(fn (): bool => $this->isPost);
        $this->request->method('all')->willReturnCallback(fn (): array => $this->requestAll);
        $this->request->method('isSet')->willReturnCallback(
            fn (string $key) => isset($this->requestSet[$key])
        );
        $this->request->method('history')->willReturn(['faq_index']);

        $this->validation = $this->createStub(FaqValidation::class);
        $this->translator = $this->createStub(FaqTranslator::class);
        $this->config = $this->createStub(FaqConfig::class);
        $this->parser = $this->createStub(FaqParser::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);

        $this->config->method('setting')->willReturnCallback(
            fn (string $key, $default = false) => $default
        );
        $this->translator->method('text')->willReturnCallback(
            static fn (string $key) => 'txt_' . $key
        );
        $this->categoryRepository->method('getAll')
            ->willReturn(['total' => 0, 'entities' => []]);
    }

    private function makeController(): CategoryController
    {
        return new CategoryController(
            $this->request,
            $this->validation,
            $this->translator,
            $this->config,
            $this->parser,
            $this->categoryRepository
        );
    }

    /** @return array<int, array{function: string, arguments: array}> */
    private function calls(string $function): array
    {
        return array_values(array_filter(
            $GLOBALS['faq_test_calls'],
            static fn (array $call): bool => $call['function'] === $function
        ));
    }

    /** @return array<int, string> recorded SMF calls in the order they happened */
    private function callOrder(): array
    {
        return array_map(
            static fn (array $call): string => $call['function'],
            $GLOBALS['faq_test_calls']
        );
    }

    /**
     * add() on POST must run the SMF session check before consuming the token,
     * then insert the new category and redirect.
     *
     * @throws Exception
     */
    public function testAddPostChecksSessionBeforeTokenAndSaves(): void
    {
        $this->isPost = true;
        $this->requestSet = ['save' => true];
        $this->requestAll = [
            CategoryEntity::NAME => 'New Category',
            'save' => 'Save',
        ];

        $entity = new CategoryEntity();
        // getEntity() and insert() have union return types PHPUnit cannot auto-generate.
        $this->categoryRepository->method('getEntity')->willReturn($entity);
        $this->validation->method('isValid')->willReturn('');

        $savedData = null;
        $this->categoryRepository->expects(self::once())->method('insert')
            ->willReturnCallback(function (array $data) use (&$savedData): CategoryEntity {
                $savedData = $data;

                return new CategoryEntity();
            });

        $this->makeController()->add();

        // Permission, session and token checks all happened, in that order.
        self::assertSame(1, count($this->calls('isAllowedTo')));
        self::assertSame(
            [Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD],
            $this->calls('isAllowedTo')[0]['arguments']
        );

        $sessionCalls = $this->calls('checkSession');
        self::assertSame(1, count($sessionCalls));
        // checkSession() records all three arguments: type, from_action, is_fatal.
        self::assertSame(['post', '', true], $sessionCalls[0]['arguments']);

        $tokenCalls = $this->calls('validateToken');
        self::assertSame(1, count($tokenCalls));
        self::assertSame(
            [CategoryController::TOKEN_ACTION, 'post', true],
            $tokenCalls[0]['arguments']
        );

        $order = $this->callOrder();
        self::assertLessThan(
            array_search('validateToken', $order, true),
            array_search('checkSession', $order, true)
        );

        // Only the real category columns reach the repository.
        self::assertSame([CategoryEntity::NAME => 'New Category'], $savedData);

        self::assertSame(1, count($this->calls('redirectexit')));
        self::assertStringContainsString('action=faq', $this->calls('redirectexit')[0]['arguments'][0]);

        // redirectexit() does not terminate under PHPUnit, so add() keeps going and
        // setTemplateVars() consumes the session message into the template context.
        global $context;
        self::assertStringContainsString('txt_info_faqCategory_insert', $context[Faq::NAME]['message']);

        // A fresh token is created for the next render, tokens are single use.
        self::assertSame(
            [CategoryController::TOKEN_ACTION, 'post'],
            $this->calls('createToken')[0]['arguments']
        );
    }

    /**
     * A validation error keeps add() from saving and leaves the error in the context.
     *
     * @throws Exception
     */
    public function testAddPostValidationErrorDoesNotSave(): void
    {
        $this->isPost = true;
        $this->requestSet = ['save' => true];
        $this->requestAll = [CategoryEntity::NAME => ''];

        $this->categoryRepository->method('getEntity')->willReturn(new CategoryEntity());
        $this->validation->method('isValid')->willReturn(CategoryEntity::NAME);
        $this->categoryRepository->expects(self::never())->method('insert');

        $this->makeController()->add();

        self::assertSame([], $this->calls('redirectexit'));

        global $context;
        self::assertSame(CategoryEntity::NAME, $context[Faq::NAME]['errors']);
    }

    /**
     * The default category can never be deleted.
     *
     * @throws Exception
     */
    public function testDeleteDefaultCategoryIsBlocked(): void
    {
        $this->requestValues = ['id' => CategoryEntity::DEFAULT_CATEGORY_ID];

        $this->categoryRepository->expects(self::never())->method('delete');
        $this->categoryRepository->expects(self::never())->method('setDefaultCategoryId');

        $this->makeController()->delete();

        // The delete permission is still checked, and the GET link carries the session check.
        self::assertSame(
            [Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE],
            $this->calls('isAllowedTo')[0]['arguments']
        );
        self::assertSame(['get', '', true], $this->calls('checkSession')[0]['arguments']);

        self::assertSame(1, count($this->calls('redirectexit')));
        self::assertSame('error_faqCategory_delete_default', $_SESSION[Faq::NAME . '_message']);
    }

    /**
     * Deleting a regular category removes it and reassigns its FAQs to the default one.
     *
     * @throws Exception
     */
    public function testDeleteNonDefaultCategoryResetsFaqsAndDeletes(): void
    {
        $this->requestValues = ['id' => 7];

        $this->categoryRepository->expects(self::once())->method('delete')->with([7])
            ->willReturnCallback(static fn (array $ids): bool => (
                $GLOBALS['faq_test_calls'][] = ['function' => 'repository_delete', 'arguments' => [$ids]]
            ) === null ? true : true);
        $this->categoryRepository->expects(self::once())->method('setDefaultCategoryId')->with(7)
            ->willReturnCallback(static function (): void {
                $GLOBALS['faq_test_calls'][] = ['function' => 'repository_setDefaultCategoryId', 'arguments' => []];
            });

        $this->makeController()->delete();

        // checkSession('get') must run before anything touches the database.
        $order = $this->callOrder();
        self::assertLessThan(
            array_search('repository_delete', $order, true),
            array_search('checkSession', $order, true)
        );
        // Both repository calls happen, the FAQs are moved to the default category.
        self::assertContains('repository_setDefaultCategoryId', $order);

        self::assertSame(1, count($this->calls('redirectexit')));
        self::assertSame('info_faqCategory_delete', $_SESSION[Faq::NAME . '_message']);
    }

    /**
     * Without an id there is nothing to delete, so the repository is never touched.
     *
     * @throws Exception
     */
    public function testDeleteWithoutIdDoesNotTouchRepository(): void
    {
        $this->requestValues = [];

        $this->categoryRepository->expects(self::never())->method('delete');
        $this->categoryRepository->expects(self::never())->method('setDefaultCategoryId');

        $this->makeController()->delete();

        // It bails out before the permission and session checks.
        self::assertSame([], $this->calls('isAllowedTo'));
        self::assertSame([], $this->calls('checkSession'));
        self::assertSame(1, count($this->calls('redirectexit')));
        self::assertSame('error_faqCategory_generic', $_SESSION[Faq::NAME . '_message']);
    }
}
