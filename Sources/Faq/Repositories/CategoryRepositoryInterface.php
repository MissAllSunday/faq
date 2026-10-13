<?php

namespace Faq\Repositories;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function setDefaultCategoryId(int $categoryId): void;
}
