<?php

namespace Faq\Repositories;

interface RepositoryInterface
{
    public function count(): int;

    public function getAll(int $start = 0, $maxIndex = 10): array;

    public function getTable(): string;
}