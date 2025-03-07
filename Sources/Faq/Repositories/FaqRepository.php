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
}