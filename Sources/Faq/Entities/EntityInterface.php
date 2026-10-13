<?php

namespace Faq\Entities;

interface EntityInterface
{
    public function getId(): int;
    public function getName(): string;
    public function toArray(): array;

    /* @return array<string, string> column name => expected type */
    public function getColumns(): array;

    /* @return array<int, string> */
    public function getRequiredFields(): array;
}