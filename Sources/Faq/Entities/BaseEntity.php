<?php

namespace Faq\Entities;

abstract class BaseEntity
{
    protected CategoryEntity | FaqEntity $entity;
    public function __construct(array $entry = [])
    {
        $this->setEntity($entry);
    }

    public function setEntity(array $entry): void
    {
        foreach ($this->castValues($entry) as $key => $value) {
            $setCall = 'set' . $this->snakeToCamel($key);
            $this->{$setCall}($value);
        }
    }

    public function toArray(): array
    {
        return \get_object_vars($this);
    }

    abstract public function getColumns(): array;
    abstract public function getTableName(): string;
    abstract public function getIndexName(): string;

    protected function snakeToCamel($input): string
    {
        return \lcfirst(\str_replace('_', '', \ucwords($input, '_')));
    }

    protected function castValues(array $data) : array
    {
        return array_map(function ($column) {
            return ctype_digit($column) ? ((int) $column) : $column;
        }, $data);
    }
}