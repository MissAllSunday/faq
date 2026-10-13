<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\FaqConfig;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(CategoryEntity $entity, FaqConfig $config)
    {
        $this->entity = $entity;

        parent::__construct($config);
    }

    /* Categories are always listed in full, so pagination is not applied. */
    /* @return array{total: int, entities: array<int, CategoryEntity>} */
    public function getAll(bool $needsPagination = true, int $start = 0): array
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

    /**
     * Reassigns every FAQ currently in the deleted category to the default one.
     */
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
}