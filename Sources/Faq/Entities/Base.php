<?php

namespace Faq\Entities;

abstract class Base
{
    public function __construct(array $entry = [])
    {
        $this->setEntry($entry);
    }

    public function setEntry(array $entry): void
    {
        foreach ($this->castValues($entry) as $key => $value) {
            $setCall = 'set' . $this->snakeToCamel($key);
            $this->{$setCall}($value);
        }
    }
    protected function snakeToCamel($input): string
    {
        return \lcfirst(\str_replace('_', '', \ucwords($input, '_')));
    }

    protected function castValues(array $data) : array
    {
        return array_map(function ($column) {
            return ctype_digit($column) ? ((int) $column) : $column;
        }, $data);
    }
}