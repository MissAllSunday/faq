<?php

namespace Faq\Entities;

class FaqEntity extends BaseEntity
{
    public const TABLE = 'faq';
    public const ID = 'id';
    public const CAT_ID = 'cat_id';
    public const LOG = 'log';
    public const TITLE = 'title';
    public const BODY = 'body';

    public const COLUMNS = [
        self::ID => 'int',
        self::CAT_ID => 'int',
        self::LOG => 'string',
        self::TITLE => 'string',
        self::BODY => 'string'
    ];

    public const DEFAULT_VALUES = [
        self::ID => '0',
        self::CAT_ID => '0',
        self::LOG => '',
        self::TITLE => '',
        self::BODY => ''
    ];

    public int $id;
    public int $catId;
    public string $log;
    public string $title;
    public string $body;

    public function getCatId(): int
    {
        return $this->catId;
    }

    public function setCatId(int $catId): void
    {
        $this->catId = $catId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLog(): string
    {
        return $this->log;
    }

    public function setLog(string $log): void
    {
        $this->log = $log;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getColumns(): array
    {
        return self::COLUMNS;
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