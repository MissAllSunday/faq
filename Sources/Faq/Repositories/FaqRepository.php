<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\FaqAdmin;
use Faq\FaqConfig;

class FaqRepository extends BaseRepository
{
    public function __construct(
        ?FaqEntity $entity = null,
        ?FaqConfig $config = null
    ) {
        $this->entity = $entity ?? new FaqEntity(FaqEntity::DEFAULT_VALUES);

        parent::__construct($config);
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
    public function searchBy(string $searchValue, int $start = 0, $limit = 0): array
    {
        $needsPagination = $limit > 0;
        $queryString = $this->buildQueryString('WHERE '. FaqEntity::TITLE .' LIKE {string:searchValue}
		    OR '. FaqEntity::BODY .' LIKE {string:searchValue}'
            . $this->buildOrderBy() . '{raw:limitQuery}');
        $params = [
            'searchValue' => '%'. $searchValue .'%',
            'limitQuery' => $this->buildLimitQuery($start, $limit),
        ];

        $request = $this->db['db_query']('', $queryString, $params);
        $entities = $this->prepareData($request);

        return [
            'total' => $needsPagination ? $this->count($queryString) : count($entities),
            'entities' => $entities,
        ];
    }

    public function getLatest(int $limit = 5): array
    {
        $queryString = $this->buildQueryString(' ORDER BY ' . FaqEntity::ID . ' DESC {raw:limitQuery}');
        $params = [
            'limitQuery' => $this->buildLimitQuery(0, $limit),
        ];

        $request = $this->db['db_query']('', $queryString, $params);
        return $this->prepareData($request);
    }
}
