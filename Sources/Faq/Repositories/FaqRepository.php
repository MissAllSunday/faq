<?php

namespace Faq\Repositories;

use Faq\Entities\FaqEntity;

class FaqRepository extends BaseRepository
{
    protected FaqEntity $entity;

    public function __construct(?FaqEntity $entity = null)
    {
        $this->entity = $entity ?? new FaqEntity();

        parent::__construct();
    }

    public function insert(array $entityData): ?FaqEntity
    {
        $this->db['db_insert'](
            'insert',
            '{db_prefix}' . FaqEntity::TABLE,
            FaqEntity::COLUMNS,
            $entityData,
            [FaqEntity::ID]
        );

        return $this->getById($entityData[FaqEntity::ID]);
    }

    public function update(array $entityData): void
    {
        $this->db['db_query'](
            '',
            'UPDATE {db_prefix}' . FaqEntity::TABLE . '
			'. $this->buildSetUpdate() .'
			WHERE '. FaqEntity::ID .' = {int:'. FaqEntity::ID .'}',
            [
                FaqEntity::ID => $entityData[FaqEntity::ID],
            ]
        );
    }

    public function getById(int $id): FaqEntity
    {
        global $smcFunc;

        $request = $this->db['db_query']('', '
		SELECT ' . (implode(',', $this->getColumns())) . '
		FROM {db_prefix}' . $this->getTable() . '
		WHERE '. FaqEntity::ID .' = {int:'. FaqEntity::ID .'}',
            [
                'id' => $id
            ]
        );

        return $this->prepareData($request);
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
		    WHERE ' . FaqEntity::ID . ' IN({array_int:ids})',
            ['ids' => $ids]
        );
    }

    function getColumns(): array
    {
        return array_keys($this->entity->getColumns());
    }

    function getTable(): string
    {
        return $this->entity->getTableName();
    }
}