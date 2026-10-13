<?php

namespace Faq\Services;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Faq\Entities\CategoryEntity;
use Faq\FaqUtils;
use Faq\Repositories\RepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;

class FaqListTest extends TestCase
{
    private MockObject $repository;
    private MockObject $utils;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        global $scripturl;

        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->utils = $this->createMock(FaqUtils::class);
    }

    /**
     * Create a partial mock of FaqList with the mocked FaqUtils injected.
     *
     * @throws Exception
     */
    private function makeFaqList(): MockObject
    {
        $faqList = $this->getMockBuilder(FaqList::class)
            ->setConstructorArgs([$this->repository])
            ->onlyMethods(['createList'])
            ->getMock();

        // Inject mocked FaqUtils into the partial mock
        $reflection = new \ReflectionClass($faqList);
        $property = $reflection->getProperty('utils');
        $property->setAccessible(true);
        $property->setValue($faqList, $this->utils);

        return $faqList;
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

        // Mock text translations (note: FaqList.php:49 passes literal '$add' due to a known bug)
        $textMap = [
            ["'\$add'" => 'Add FAQ'],
            ['add_title' => 'Add FAQ'],
            ['edit_id' => 'ID'],
            ['edit_name' => 'Name'],
            ['edit' => 'Edit'],
            ['delete' => 'Delete'],
            ['no_search_results' => 'No results found'],
        ];
        $this->utils->method('text')
            ->willReturnCallback(fn(string $key) => $textMap[$key] ?? '');

        // Mock setting
        $this->utils->method('setting')
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

        // Test column structure
        $this->assertArrayHasKey('id', $listOptions['columns']);
        $this->assertArrayHasKey('name', $listOptions['columns']);
        $this->assertArrayHasKey('edit', $listOptions['columns']);
        $this->assertArrayHasKey('delete', $listOptions['columns']);
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

        // Mock text translations (note: FaqList.php:49 passes literal '$add' due to a known bug)
        $textMap = [
            ["'\$add'" => 'Add Category'],
            ['category_add_title' => 'Add Category'],
            ['edit_id' => 'ID'],
            ['edit_name' => 'Name'],
            ['edit' => 'Edit'],
            ['delete' => 'Delete'],
            ['no_search_results' => 'No results found'],
        ];
        $this->utils->method('text')
            ->willReturnCallback(fn(string $key) => $textMap[$key] ?? '');

        // Mock setting
        $this->utils->method('setting')
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
        $this->assertEquals('localhost?action=faqCategory;sa=manage', $listOptions['base_href']);
        $this->assertTrue(str_contains($listOptions['base_href'], 'action=faqCategory'));

        // Test delete button behavior for default category
        $deleteFunction = $listOptions['columns']['delete']['data']['function'];
        $result = $deleteFunction($mockEntity);
        $this->assertIsString($result);
    }
}
