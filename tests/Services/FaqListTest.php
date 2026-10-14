<?php

namespace Faq\Services;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Faq\Entities\CategoryEntity;
use Faq\FaqConfig;
use Faq\FaqTranslator;
use Faq\Repositories\RepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;

class FaqListTest extends TestCase
{
    private MockObject $repository;
    private MockObject $translator;
    private MockObject $config;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        global $scripturl;

        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->translator = $this->createMock(FaqTranslator::class);
        $this->config = $this->createMock(FaqConfig::class);
    }

    /**
     * Create a partial mock of FaqList with the mocked collaborators injected
     * through the constructor.
     *
     * @throws Exception
     */
    private function makeFaqList(): MockObject
    {
        return $this->getMockBuilder(FaqList::class)
            ->setConstructorArgs([$this->repository, $this->translator, $this->config, 0])
            ->onlyMethods(['createList'])
            ->getMock();
    }

    /**
     * @throws Exception
     */
    public function testBuildForFaqTable(): void
    {
        // Mock repository responses
        $this->repository->method('count')->willReturn(5);
        $this->repository->method('getTable')->willReturn('faq_table');

        // Mock entity
        $mockEntity = $this->createMock(CategoryEntity::class);
        $mockEntity->method('getId')->willReturn(1);
        $mockEntity->method('getName')->willReturn('Test FAQ');

        $this->repository->method('getAll')
            ->willReturn(['total' => 5, 'entities' => [$mockEntity]]);

        // Mock text translations, keyed the way FaqTranslator::text() is called.
        $textMap = [
            'add_title' => 'Add FAQ',
            'category_add_title' => 'Add Category',
            'edit_id' => 'ID',
            'edit_name' => 'Name',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'no_search_results' => 'No results found',
        ];
        $this->translator->method('text')
            ->willReturnCallback(fn(string $key) => $textMap[$key] ?? '');

        // Mock setting
        $this->config->method('setting')
            ->with('num_faqs', 0)
            ->willReturn(10);

        // Capture the createList call
        $listOptions = null;
        $faqList = $this->makeFaqList();

        $faqList->method('createList')
            ->willReturnCallback(function ($options) use (&$listOptions) {
                $listOptions = $options;
            });

        // Execute the build method
        $faqList->build('Test Message');

        // Assertions
        $this->assertEquals(FaqList::ID, $listOptions['id']);
        $this->assertEquals(10, $listOptions['items_per_page']);
        $this->assertEquals('Test Message', $listOptions['additional_rows'][0]['value']);
        $this->assertEquals('Add FAQ', $listOptions['title']);

        // Test column structure
        $this->assertArrayHasKey('id', $listOptions['columns']);
        $this->assertArrayHasKey('name', $listOptions['columns']);
        $this->assertArrayHasKey('edit', $listOptions['columns']);
        $this->assertArrayHasKey('delete', $listOptions['columns']);
    }

    /**
     * Regression: the list title must be resolved through the translator
     * (translator->text('add_title')), never a literal '$add' string.
     *
     * @throws Exception
     */
    public function testBuildResolvesTitleViaTranslatorAddTitle(): void
    {
        $this->repository->method('getTable')->willReturn('faq_table');
        $this->repository->method('getAll')
            ->willReturn(['total' => 0, 'entities' => []]);
        $this->config->method('setting')->willReturn(10);

        // The title (and the bottom "add" button) must be sourced from the
        // translator using the 'add_title' key.
        $this->translator
            ->expects($this->atLeastOnce())
            ->method('text')
            ->willReturnCallback(function (string $key) {
                return match ($key) {
                    'add_title' => 'Add FAQ',
                    default => '',
                };
            });

        $listOptions = null;
        $faqList = $this->makeFaqList();
        $faqList->method('createList')
            ->willReturnCallback(function ($options) use (&$listOptions) {
                $listOptions = $options;
            });

        $faqList->build();

        $this->assertSame('Add FAQ', $listOptions['title']);
    }

    /**
     * @throws Exception
     */
    public function testBuildForCategoryTable(): void
    {
        // Mock repository responses
        $this->repository->method('count')->willReturn(3);
        $this->repository->method('getTable')->willReturn('faq_categories');

        // Mock entity
        $mockEntity = $this->createMock(CategoryEntity::class);
        $mockEntity->method('getId')->willReturn(CategoryEntity::DEFAULT_CATEGORY_ID);
        $mockEntity->method('getName')->willReturn('Default Category');

        $this->repository->method('getAll')
            ->willReturn(['total' => 3, 'entities' => [$mockEntity]]);

        // Mock text translations, keyed the way FaqTranslator::text() is called.
        $textMap = [
            'add_title' => 'Add FAQ',
            'category_add_title' => 'Add Category',
            'edit_id' => 'ID',
            'edit_name' => 'Name',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'no_search_results' => 'No results found',
        ];
        $this->translator->method('text')
            ->willReturnCallback(fn(string $key) => $textMap[$key] ?? '');

        // Mock setting
        $this->config->method('setting')
            ->with('num_faqs', 0)
            ->willReturn(10);

        // Capture the createList call
        $listOptions = null;
        $faqList = $this->makeFaqList();

        $faqList->method('createList')
            ->willReturnCallback(function ($options) use (&$listOptions) {
                $listOptions = $options;
            });

        // Execute the build method
        $faqList->build();

        // Assertions
        $this->assertEquals('Add Category', $listOptions['title']);
        $this->assertEquals('localhost?action=faqCategory;sa=manage', $listOptions['base_href']);
        $this->assertTrue(str_contains($listOptions['base_href'], 'action=faqCategory'));

        // Test delete button behavior for default category
        $deleteFunction = $listOptions['columns']['delete']['data']['function'];
        $result = $deleteFunction($mockEntity);
        $this->assertIsString($result);
    }
}
