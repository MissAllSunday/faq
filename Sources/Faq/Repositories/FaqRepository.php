<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;

class FaqRepository extends BaseRepository
{
    public function __construct(?FaqEntity $entity = null)
    {
        $this->entity = $entity ?? new FaqEntity(FaqEntity::DEFAULT_VALUES);

        parent::__construct();
    }

    /* @return array[EntityInterface] */
    public function getByCatId(int $id): array
    {
        $request = $this->db['db_query']('', '
		SELECT ' . (implode(',', $this->getColumns())) . '
		FROM {db_prefix}' . $this->getTable() . '
		WHERE '. FaqEntity::CAT_ID .' = {int:id}',
            [
                'id' => $id
            ]
        );

        return $this->prepareData($request);
    }

    /* @return array[EntityInterface] */
    public function searchBy(string $searchValue): array
    {
        $request = $this->db['db_query']('', '
		SELECT ' . (implode(',', $this->getColumns())) . '
		FROM {db_prefix}' . $this->getTable() . '
		WHERE '. FaqEntity::TITLE .' LIKE {string:searchValue}
		    OR '. FaqEntity::BODY .' LIKE {string:searchValue}',
            [
                'searchValue' => '%'. $searchValue .'%'
            ]
        );

        return $this->prepareData($request);
    }
}