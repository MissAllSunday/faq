<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;

class CategoryRepository extends BaseRepository
{    public function __construct(?CategoryEntity $entity = null)
    {
        $this->entity = $entity ?? new CategoryEntity();

        parent::__construct();
    }

    public function getAll(): array
    {
        return $this->prepareData($this->db['db_query'](
            '',
            'SELECT {raw:columns}
			FROM {db_prefix}{raw:from}',
            [
                'from' => CategoryEntity::TABLE,
                'columns' => implode(',', array_keys(CategoryEntity::COLUMNS)),
            ]
        ));
    }
}