<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;

class CategoryRepository extends BaseRepository
{
    public function __construct(?CategoryEntity $entity = null)
    {
        $this->entity = $entity ?? new CategoryEntity();

        parent::__construct();
    }

    public function getAll($needsPagination = true, int $start = 0): array
    {
        $queryString = '
            SELECT ' . (implode(',', $this->getColumns())) . '
			FROM {db_prefix}' . $this->getTable();
        $params = [];

        return [
            'total' => 0,
            'entities' => $this->prepareData($this->db['db_query']('', $queryString, $params)),
        ];
    }

    public function getById(int $id): FaqEntity | CategoryEntity
    {
        $request = $this->db['db_query']('', '
		SELECT ' . (implode(',', $this->getColumns())) . '
		FROM {db_prefix}' . $this->getTable() . '
		WHERE category_id = {int:category_id}',
            [
                'category_id' => $id
            ]
        );

        return $this->prepareData($request)[$id];
    }
}