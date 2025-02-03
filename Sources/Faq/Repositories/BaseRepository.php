<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;

abstract class BaseRepository
{
    protected $db;
    protected FaqEntity | CategoryEntity $entity;

    public function __construct()
    {
        global $smcFunc;

        $this->db = $smcFunc;
    }

    public function insert(array $entityData): FaqEntity | CategoryEntity
    {
        $indexName = $this->entity->getIndexName();
        $columns = $this->entity->getColumns();
        unset($columns[$indexName]);

        $this->db['db_insert'](
            'insert',
            '{db_prefix}' . $this->getTable(),
            $columns,
            $entityData,
            [$indexName]
        );

        return $this->getById($this->getInsertedId());
    }

    public function update(array $entityData): void
    {
        $indexName = $this->entity->getIndexName();

        $this->db['db_query'](
            '',
            'UPDATE {db_prefix}' . $this->getTable() . '
			'. $this->buildSetUpdate() .'
			WHERE '. $indexName .' = {int:'. $indexName .'}',
            [
                $indexName => $entityData[$indexName],
            ]
        );
    }

    public function getById(int $id): FaqEntity | CategoryEntity
    {
        $indexName = $this->entity->getIndexName();

        $request = $this->db['db_query']('', '
		SELECT ' . (implode(',', $this->getColumns())) . '
		FROM {db_prefix}' . $this->getTable() . '
		WHERE '. $indexName .' = {int:'. $indexName .'}',
            [
                'id' => $id
            ]
        );

        return $this->prepareData($request)[$id];
    }

    public function delete(array $ids): bool
    {
        if (empty($ids)) {
            return false;
        }

        return $this->db['db_query'](
            '','
            DELETE
			FROM {db_prefix}' . FaqEntity::TABLE . '
		    WHERE ' . $this->entity->getIndexName() . ' IN({array_int:ids})',
            ['ids' => $ids]
        );
    }

    public function getInsertedId(): int
    {
        return $this->db['db_insert_id']('{db_prefix}' . $this->entity->getTableName(), $this->entity->getIndexName());
    }

    public function getEntity(): FaqEntity
    {
        return $this->entity;
    }


    protected function buildSetUpdate(): string
    {
        $set = 'SET ';
        foreach ($this->entity->getColumns() as $name => $type) {
            $set .= ' ' . $name . ' = {' . $type . ':'. $name .'},';
        }

        return rtrim($set, ',');
    }

    protected function prepareData(object $request): array
    {
        $entities = [];

        while ($row = $this->fetchAssoc($request)) {
            $newEntity = $this->buildEntity();
            $entities[$row[$this->entity->getIndexName()]] = $newEntity->setEntity(array_map(function ($column) {
                return ctype_digit($column) ? ((int) $column) : $column;
            }, $row));
        }
        $this->freeResult($request);

        return $entities;
    }

    protected function buildEntity(): FaqEntity | CategoryEntity
    {
        return match ($this->entity->getIndexName()) {
            CategoryEntity::ID => new CategoryEntity(),
            default => new FaqEntity(),
        };
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

    protected function getColumns(): array
    {
        return array_keys($this->entity->getColumns());
    }

    protected function getTable(): string
    {
        return $this->entity->getTableName();
    }
}