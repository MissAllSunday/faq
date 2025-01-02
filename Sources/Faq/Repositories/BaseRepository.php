<?php

namespace Faq\Repositories;

abstract class BaseRepository
{
    protected $db;

    public function __construct()
    {
        global $smcFunc;

        $this->db = $smcFunc;
    }

    public function getInsertedId(): int
    {
        return $this->db['db_insert_id']('{db_prefix}' . $this->entity->getTableName(), $this->entity->getIndex());
    }


    protected function buildSetUpdate(): string
    {
        $set = 'SET ';
        foreach ($this->entity->getColumns() as $name => $type) {
            $set .= ' ' . $name . ' = {' . $type . ':'. $name .'},';
        }

        return rtrim($set, ',');
    }

    protected function prepareData(object $request): ?object
    {

        // This only works for a single entity but thats OK
        while ($row = $this->fetchAssoc($request)) {
            $this->entity->setEntity(array_map(function ($column) {
                return ctype_digit($column) ? ((int) $column) : explode(',', $column);
            }, $row));
        }
        $this->freeResult($request);

        return $this->entity;
    }

    protected function fetchAssoc($result): ?array
    {
        return $this->db['db_fetch_assoc']($result);
    }
    protected function fetchRow($result): ?array
    {
        return $this->db['db_fetch_row']($result);
    }
    protected function freeResult($result): void
    {
        $this->db['db_free_result']($result);
    }
}