<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\EntityInterface;
use Faq\Entities\FaqEntity;
use Faq\FaqAdmin;
use Faq\FaqUtils;

abstract class BaseRepository implements RepositoryInterface
{
    protected $db;
    protected FaqEntity | CategoryEntity $entity;
    protected FaqUtils $utils;
    protected string $sortMethod;
    protected string $sortOrder;

    public function __construct()
    {
        global $smcFunc;

        $this->db = $smcFunc;
        $this->utils = new FaqUtils();
        $this->sortMethod = $this->utils->setting(FaqAdmin::SETTINGS_SORT_METHOD, 'id');
        $this->sortOrder = $this->utils->setting(FaqAdmin::SETTINGS_SORT_ORDER, 'ASC');
    }

    public function insert(array $entityData): FaqEntity | CategoryEntity
    {
        $indexName = $this->entity->getIndexName();
        $columns = $this->entity->getColumns();
        unset($columns[$indexName]);
        ksort($columns);
        ksort($entityData);

        $this->db['db_insert'](
            'insert',
            '{db_prefix}' . $this->getTable(),
            $columns,
            $entityData,
            [$indexName]
        );

        return $this->getById($this->getInsertedId());
    }

    public function update(array $entityData, int $id): void
    {
        $indexName = $this->entity->getIndexName();

        $this->db['db_query'](
            '',
            'UPDATE {db_prefix}' . $this->getTable() . '
			'. $this->buildSetUpdate($entityData) .'
			WHERE '. $indexName .' = {int:id}',
            array_merge($entityData, ['id' => $id]),
        );
    }

    public function setDefaultCategoryId(int $categoryId): void
    {
        $this->db['db_query'](
            '',
            'UPDATE {db_prefix}' . FaqEntity::TABLE . '
			SET '. FaqEntity::CAT_ID .' = {int:defaultId}
			WHERE '. FaqEntity::CAT_ID .' = {int:id}',
            ['defaultId' => CategoryEntity::DEFAULT_CATEGORY_ID, 'id' => $categoryId],
        );
    }

    public function getById(int $id): FaqEntity | CategoryEntity
    {
        $indexName = $this->entity->getIndexName();

        $request = $this->db['db_query']('', '
		SELECT ' . (implode(',', $this->getColumns())) . '
		FROM {db_prefix}' . $this->getTable() . '
		WHERE '. $indexName .' = {int:id}'
            . $this->buildOrderBy(),
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
			FROM {db_prefix}' . $this->getTable() . '
		    WHERE ' . $this->entity->getIndexName() . ' IN({array_int:ids})',
            ['ids' => $ids]
        );
    }

    public function getInsertedId(): int
    {
        return $this->db['db_insert_id']('{db_prefix}' . $this->entity->getTableName(), $this->entity->getIndexName());
    }

    public function getEntity(): FaqEntity | CategoryEntity
    {
        return $this->entity;
    }

    public function count(string $queryString = '', array $params = []): int
    {
        $result = $this->db['db_query']('', $queryString,
            $params
        );
        $countTotal = $this->numRows($result);
        $this->freeResult($result);

        return $countTotal;
    }

    public function getAll($needsPagination = true, int $start = 0): array
    {
        $queryStringCount = $queryString = '
            SELECT ' . (implode(',', $this->getColumns())) . '
			FROM {db_prefix}' . $this->getTable()
            . $this->buildOrderBy();
        $params = [];

        if ($needsPagination) {
            $queryString .= '
			{raw:limitQuery}';
            $params = array_merge($params, ['limitQuery' => $this->buildLimitQuery($start)]);
        }

        return [
            'total' => $needsPagination ? $this->count($queryStringCount) : 0,
            'entities' => $this->prepareData($this->db['db_query']('', $queryString, $params)),
        ];
    }

    public function buildPagination(int $start, string $paginationUrl, int $total = 0): string
    {
        $limit = $this->utils->setting(FaqAdmin::SETTINGS_PAGINATION);

        if (empty($limit) || !$total) {
            return '';
        }

       return constructPageIndex(
            $paginationUrl,
            $start,
            $total,
            $limit
        );
    }

    protected function buildLimitQuery(int $start): string
    {
        $limit = $this->utils->setting(FaqAdmin::SETTINGS_PAGINATION, 0);

        return $limit ? strtr('LIMIT {start}, {limit}', [
            '{start}' => $start,
            '{limit}' => $limit
        ]) : '';

    }

    protected function buildSetUpdate(array $entityData = []): string
    {
        $set = 'SET ';
        $columns = $this->entity->getColumns();
        foreach ($entityData as $name => $type) {
            $set .= ' ' . $name . ' = {' . $columns[$name] . ':'. $name .'},';
        }

        return rtrim($set, ',');
    }

    protected function buildOrderBy(): string
    {
        return '
        ORDER BY ' . $this->sortMethod . ' ' . $this->sortOrder;
    }

    /* @return array[EntityInterface] */
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

    protected function numRows($result): int
    {
        return $this->db['db_num_rows']($result);
    }

    protected function freeResult($result): void
    {
        $this->db['db_free_result']($result);
    }

    protected function getColumns(): array
    {
        return array_keys($this->entity->getColumns());
    }

    public function getTable(): string
    {
        return $this->entity->getTableName();
    }
}