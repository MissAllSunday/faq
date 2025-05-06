<?php

namespace Faq\Entities;

interface EntityInterface
{
    public function getId(): int;
    public function getName(): string;
    public function toArray(): array;
}