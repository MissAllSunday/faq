<?php

namespace Faq\Repositories;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;

interface RepositoryInterface
{
    public function count(): int;

    public function getAll($needsPagination = true, int $start = 0): array;

    public function buildPagination(int $start, string $paginationUrl): string;

    public function getTable(): string;

    public function getById(int $id): FaqEntity | CategoryEntity;

    public function getEntity(): FaqEntity | CategoryEntity;

    public function delete(array $ids): bool;

    public function update(array $entityData, int $id): void;

    public function setDefaultCategoryId(int $categoryId): void;
}