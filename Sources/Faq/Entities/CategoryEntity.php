<?php

namespace Faq\Entities;

class CategoryEntity extends BaseEntity
{
    public const TABLE = 'faq_categories';
    public const ID = 'category_id';
    public const NAME = 'category_name';
    public const COLUMNS = [
        self::ID => 'int',
        self::NAME => 'string',
    ];
    public int $id;
    public string $name = '';

    public function getCategoryName(): string
    {
        return $this->name;
    }

    public function setCategoryName(string $name): void
    {
        $this->name = $name;
    }

    public function getCategoryId(): int
    {
        return $this->id;
    }

    public function setCategoryId(int $id): void
    {
        $this->id = $id;
    }

    public function getColumns(): array
    {
        return self::COLUMNS;
    }

    public function getRequiredFields(): array
    {
        return [
            self::NAME,
        ];
    }

    public function getTableName(): string
    {
        return self::TABLE;
    }

    public function getIndexName(): string
    {
        return self::ID;
    }
}