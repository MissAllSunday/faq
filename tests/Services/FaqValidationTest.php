<?php

namespace Faq\Services;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Faq\Services\FaqValidation;
use Faq\Entities\FaqEntity;
use Faq\Entities\CategoryEntity;
use Faq\ValidatorError;
use Faq\TypeError;

class FaqValidationTest extends TestCase
{
    private FaqValidation $validator;

    protected function setUp(): void
    {
        $this->validator = new FaqValidation();
    }

    /**
     * @throws Exception
     */
    public function testValidFaqEntityValidation(): void
    {
        $entity = $this->createMock(FaqEntity::class);
        $entity->method('getColumns')->willReturn([
            'title' => 'string',
            'body' => 'string',
            'cat_id' => 'int'
        ]);
        $entity->method('getRequiredFields')->willReturn(['title', 'body']);

        $data = [
            'title' => 'Test FAQ',
            'body' => 'Test body content',
            'cat_id' => 1
        ];

        $result = $this->validator->isValid($entity, $data);
        $this->assertEmpty($result);
    }

    public function testValidCategoryEntityValidation(): void
    {
        $entity = $this->createMock(CategoryEntity::class);
        $entity->method('getColumns')->willReturn([
            'name' => 'string'
        ]);
        $entity->method('getRequiredFields')->willReturn(['name']);

        $data = [
            'name' => 'Test Category'
        ];

        $result = $this->validator->isValid($entity, $data);
        $this->assertEmpty($result);
    }

    public function testMissingRequiredFields(): void
    {
        $entity = $this->createMock(FaqEntity::class);
        $entity->method('getColumns')->willReturn([
            'title' => 'string',
            'body' => 'string'
        ]);
        $entity->method('getRequiredFields')->willReturn(['title', 'body']);

        $data = [
            'title' => 'Test FAQ'
            // body is missing
        ];

        $result = $this->validator->isValid($entity, $data);
        $this->assertEquals('body', $result);
    }

    /**
     * @throws Exception
     */
    public function testInvalidStringType(): void
    {
        $entity = $this->createMock(FaqEntity::class);
        $entity->method('getColumns')->willReturn([
            'title' => 'string',
            'body' => 'string',
        ]);
        $entity->method('getRequiredFields')->willReturn(['title']);

        $data = [
            'title' => 123,
            'body' => 'body content'
        ];

        $errorReturned = $this->validator->isValid($entity, $data);

        $this->assertEquals('validation_type', $errorReturned);
    }

    /**
     * @throws Exception
     */
    public function testInvalidIntType(): void
    {
        $entity = $this->createMock(CategoryEntity::class);
        $entity->method('getColumns')->willReturn([
            'id' => 'int'
        ]);
        $entity->method('getRequiredFields')->willReturn(['id']);

        $data = [
            'id' => 'not an integer'
        ];

        $errorReturned = $this->validator->isValid($entity, $data);

        $this->assertEquals('validation_type', $errorReturned);
    }

    /**
     * @throws Exception
     */
    public function testEmptyDataValidation(): void
    {
        $entity = $this->createMock(FaqEntity::class);
        $entity->method('getColumns')->willReturn([
            'title' => 'string',
            'body' => 'string'
        ]);
        $entity->method('getRequiredFields')->willReturn(['title', 'body']);

        $result = $this->validator->isValid($entity, []);
        $this->assertEquals('title, body', $result);
    }

    /**
     * @throws Exception
     */
    public function testNullValuesAreConsideredMissing(): void
    {
        $entity = $this->createMock(FaqEntity::class);
        $entity->method('getColumns')->willReturn([
            'title' => 'string',
            'body' => 'string'
        ]);
        $entity->method('getRequiredFields')->willReturn(['title', 'body']);

        $data = [
            'title' => null,
            'body' => null
        ];

        $result = $this->validator->isValid($entity, $data);
        $this->assertEquals('title, body', $result);
    }
}