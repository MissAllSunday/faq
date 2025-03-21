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
}