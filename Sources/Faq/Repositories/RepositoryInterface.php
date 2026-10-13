<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;

interface RepositoryInterface
{
    public function count(): int;

    /* @return array{total: int, entities: array<int, FaqEntity|CategoryEntity>} */
    public function getAll(bool $needsPagination = true, int $start = 0): array;

    public function buildPagination(int $start, string $paginationUrl, int $total = 0): string;

    public function getTable(): string;

    public function getById(int $id): FaqEntity | CategoryEntity;

    public function getEntity(): FaqEntity | CategoryEntity;

    public function delete(array $ids): bool;

    public function insert(array $entityData): FaqEntity | CategoryEntity;

    public function update(array $entityData, int $id): void;
}
