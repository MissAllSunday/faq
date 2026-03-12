<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\FaqAdmin;

class FaqRepository extends BaseRepository
{
    public function __construct(?FaqEntity $entity = null)
    {
        $this->entity = $entity ?? new FaqEntity(FaqEntity::DEFAULT_VALUES);

        parent::__construct();
    }

    public function getAll(int $start = 0, int $limit = 0): array
    {
        $needsPagination = $limit > 0;
        $queryStringCount = $queryString = '
            SELECT ' . (implode(',', $this->getColumns())) . '
			FROM {db_prefix}' . $this->getTable()
            . $this->buildOrderBy();
        $params = [];

        if ($needsPagination) {
            $queryString .= '
			{raw:limitQuery}';
            $params = array_merge($params, ['limitQuery' => $this->buildLimitQuery($start, $limit)]);
        }

        return [
            'total' => $needsPagination ? $this->count($queryStringCount) : count($entities),
            'entities' => $entities,
        ];
    }

    /* @return array[EntityInterface] */
    public function getByCatId(int $id, int $start = 0): array
    {
        $queryString = '
		SELECT ' . (implode(',', $this->getColumns())) . '
		FROM {db_prefix}' . $this->getTable() . '
		WHERE '. FaqEntity::CAT_ID .' = {int:id}'
            . $this->buildOrderBy();
        $params = [
            'id' => $id,
        ];
        $request = $this->db['db_query']('', $queryString . '
		{raw:limitQuery}', array_merge($params, ['limitQuery' => $this->buildLimitQuery($start)])
        );

        return [
            'total' => $this->count($queryString, $params),
            'entities' => $this->prepareData($request),
        ];
    }

    /* @return array[EntityInterface] */
    public function searchBy(string $searchValue, int $start = 0): array
    {
        $queryString = '
		SELECT ' . (implode(',', $this->getColumns())) . '
		FROM {db_prefix}' . $this->getTable() . '
		WHERE '. FaqEntity::TITLE .' LIKE {string:searchValue}
		    OR '. FaqEntity::BODY .' LIKE {string:searchValue}'
            . $this->buildOrderBy();
        $params = [
            'searchValue' => '%'. $searchValue .'%',
        ];

        $request = $this->db['db_query']('',$queryString . '{raw:limitQuery}',
            array_merge($params, ['limitQuery' => $this->buildLimitQuery($start)])
        );

        return [
            'total' => $this->count($queryString, $params),
            'entities' => $this->prepareData($request),
        ];
    }
}