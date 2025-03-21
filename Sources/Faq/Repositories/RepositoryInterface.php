<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;

interface RepositoryInterface
{
    public function count(): int;

    public function getAll(int $start = 0, $maxIndex = 10): array;

    public function getTable(): string;

    public function getById(int $id): FaqEntity | CategoryEntity;

    public function getEntity(): FaqEntity | CategoryEntity;

    public function delete(array $ids): bool;
}