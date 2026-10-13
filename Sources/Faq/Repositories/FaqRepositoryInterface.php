<?php

namespace Faq\Repositories;

use Faq\Entities\FaqEntity;

interface FaqRepositoryInterface extends RepositoryInterface
{
    /* @return array{total: int, entities: array<int, FaqEntity>} */
    public function getByCatId(int $id, int $start = 0): array;

    /* @return array{total: int, entities: array<int, FaqEntity>} */
    public function searchBy(string $searchValue, int $start = 0): array;
}
