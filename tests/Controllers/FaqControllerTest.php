<?php

namespace Faq\Controllers;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\Faq;
use Faq\FaqAdmin;
use Faq\FaqConfig;
use Faq\FaqParser;
use Faq\FaqRequest;
use Faq\FaqTranslator;
use Faq\Repositories\CategoryRepositoryInterface;
use Faq\Repositories\FaqRepositoryInterface;
use Faq\Services\FaqValidation;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class FaqControllerTest extends TestCase
{
    private Stub $request;
    private Stub $validation;
    private Stub $translator;
    private Stub $config;
    private Stub $parser;
    private MockObject $faqRepository;
    private Stub $categoryRepository;

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
            'current_url' => 'localhost?action=faq',
            // setSubAction() normally populates this before a sub action runs;
            // seed it so overrideLinkTree() has something to replace.
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
        $this->faqRepository = $this->createMock(FaqRepositoryInterface::class);
        $this->categoryRepository = $this->createStub(CategoryRepositoryInterface::class);

        // Sensible defaults for the collaborators the sub actions touch.
        $this->config->method('setting')->willReturnCallback(
            fn (string $key, $default = false) => $default
        );
        $this->translator->method('text')->willReturnCallback(
            static fn (string $key) => 'txt_' . $key
        );
        $this->parser->method('parse')->willReturnCallback(
            static fn (array $data) => ['title' => $data['title'] ?? '', 'body' => $data['body'] ?? '']
        );
        $this->categoryRepository->method('getAll')
            ->willReturn(['total' => 0, 'entities' => []]);
    }

    private function makeController(): FaqController
    {
        return new FaqController(
            $this->request,
            $this->validation,
            $this->translator,
            $this->config,
            $this->parser,
            $this->faqRepository,
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

    /**
     * @throws Exception
     */
    public function testAddPostWithoutValidTokenDoesNotSave(): void
    {
        $this->isPost = true;
        $this->requestSet = ['save' => true];
        $this->requestAll = [
            FaqEntity::TITLE => 'New FAQ',
            FaqEntity::BODY => 'Body text',
            FaqEntity::CAT_ID => 1,
        ];

        $entity = new FaqEntity(FaqEntity::DEFAULT_VALUES);
        $this->faqRepository->expects(self::once())->method('getEntity')->willReturn($entity);
        // A validation error keeps the save from happening.
        $this->validation->method('isValid')->willReturn('body');

        $this->makeController()->add();

        // validateToken() records all three arguments: action, type, reset.
        self::assertSame(1, count($this->calls('validateToken')));
        self::assertSame([FaqController::TOKEN_ACTION, 'post', true], $this->calls('validateToken')[0]['arguments']);
        // A validation error means no save and therefore no redirect.
        self::assertSame([], $this->calls('redirectexit'));

        global $context;
        self::assertSame('body', $context[Faq::NAME]['errors']);
    }

    /**
     * @throws Exception
     */
    public function testAddPostValidSaveInsertsAndRedirects(): void
    {
        $this->isPost = true;
        $this->requestSet = ['save' => true];
        $this->requestAll = [
            FaqEntity::TITLE => 'New FAQ',
            FaqEntity::BODY => 'Body text',
            FaqEntity::CAT_ID => 1,
        ];

        $entity = new FaqEntity(FaqEntity::DEFAULT_VALUES);
        $this->faqRepository->method('getEntity')->willReturn($entity);
        $this->validation->method('isValid')->willReturn('');
        // insert() has a union return type PHPUnit cannot auto-generate.
        $this->faqRepository->expects(self::once())->method('insert')
            ->willReturn(new FaqEntity(FaqEntity::DEFAULT_VALUES));

        $this->makeController()->add();

        self::assertSame(1, count($this->calls('redirectexit')));
        self::assertStringContainsString('action=faq', $this->calls('redirectexit')[0]['arguments'][0]);
    }

    /**
     * @throws Exception
     */
    public function testAddChecksPermission(): void
    {
        // getEntity() has a union return type PHPUnit cannot auto-generate.
        $this->faqRepository->expects(self::once())->method('getEntity')->willReturn(new FaqEntity(FaqEntity::DEFAULT_VALUES));

        $this->makeController()->add();

        self::assertSame(1, count($this->calls('isAllowedTo')));
        self::assertSame([Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD], $this->calls('isAllowedTo')[0]['arguments']);
    }

    /**
     * @throws Exception
     */
    public function testDeleteChecksSessionBeforeRepositoryDelete(): void
    {
        $this->requestValues = ['id' => 7];
        // Record the repository call so we can assert it happens after checkSession().
        $this->faqRepository->expects(self::once())->method('delete')->with([7])
            ->willReturnCallback(static fn (array $ids): bool => (
                $GLOBALS['faq_test_calls'][] = ['function' => 'repository_delete', 'arguments' => [$ids]]
            ) === null ? true : true);

        $this->makeController()->delete();

        $sessionCalls = $this->calls('checkSession');
        self::assertSame(1, count($sessionCalls));
        // checkSession() records all three arguments: type, from_action, is_fatal.
        self::assertSame(['get', '', true], $sessionCalls[0]['arguments']);

        // checkSession must run before the repository delete.
        $order = array_map(
            static fn (array $call): string => $call['function'],
            $GLOBALS['faq_test_calls']
        );
        self::assertLessThan(
            array_search('repository_delete', $order, true),
            array_search('checkSession', $order, true)
        );

        self::assertSame(1, count($this->calls('redirectexit')));
    }

    /**
     * @throws Exception
     */
    public function testDeleteWithoutIdDoesNotTouchRepository(): void
    {
        $this->requestValues = [];
        $this->faqRepository->expects(self::never())->method('delete');

        $this->makeController()->delete();

        self::assertSame(1, count($this->calls('redirectexit')));
    }

    /**
     * @throws Exception
     */
    public function testSearchGetRedirectsToIndex(): void
    {
        $this->isPost = false;
        $this->faqRepository->expects(self::never())->method('searchBy');

        $this->makeController()->search();

        self::assertSame(1, count($this->calls('redirectexit')));
        // A GET search never reaches the permission check.
        self::assertSame([], $this->calls('isAllowedTo'));
    }

    /**
     * @throws Exception
     */
    public function testSearchPostEmptyValueRedirectsWithError(): void
    {
        $this->isPost = true;
        $this->requestValues = ['search_value' => ''];
        $this->faqRepository->expects(self::never())->method('searchBy');

        $this->makeController()->search();

        self::assertSame(1, count($this->calls('redirectexit')));
    }

    /**
     * @throws Exception
     */
    public function testSearchPostWithValueRunsQuery(): void
    {
        $this->isPost = true;
        $this->requestValues = ['search_value' => 'needle'];
        $this->faqRepository->expects(self::once())->method('searchBy')
            ->with('needle', 0)
            ->willReturn(['total' => 1, 'entities' => [new FaqEntity(FaqEntity::DEFAULT_VALUES)]]);

        $this->makeController()->search();

        self::assertSame(0, count($this->calls('redirectexit')));
    }

    /**
     * @throws Exception
     */
    public function testSingleWithoutIdRedirectsToIndex(): void
    {
        $this->requestValues = [];
        $this->faqRepository->expects(self::never())->method('getById');

        $this->makeController()->single();

        self::assertSame(1, count($this->calls('redirectexit')));
    }

    /**
     * @throws Exception
     */
    public function testSingleLoadsEntityAndSetsTemplateVars(): void
    {
        $this->requestValues = ['id' => 3];
        $entity = new FaqEntity(FaqEntity::DEFAULT_VALUES);
        $entity->setId(3);
        $entity->setTitle('My FAQ');
        $this->faqRepository->method('getById')->with(3)->willReturn($entity);

        $this->makeController()->single();

        self::assertSame([], $this->calls('redirectexit'));
        global $context;
        self::assertArrayHasKey(Faq::NAME, $context);
        self::assertCount(1, $context[Faq::NAME]['entities']);
    }
}
