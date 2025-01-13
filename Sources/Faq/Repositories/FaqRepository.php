<?php

namespace Faq\Repositories;

use Faq\Entities\FaqEntity;

class FaqRepository extends BaseRepository
{
    public function __construct(?FaqEntity $entity = null)
    {
        $this->entity = $entity ?? new FaqEntity(FaqEntity::DEFAULT_VALUES);

        parent::__construct();
    }

    public function getAll(): array
    {
        //TODO pagination
        return $this->prepareData($this->db['db_query'](
            '',
            'SELECT {raw:columns}
			FROM {db_prefix}{raw:from}',
            [
                'from' => FaqEntity::TABLE,
                'columns' => implode(',', array_keys(FaqEntity::COLUMNS)),
            ]
        ));
    }
}