<?php

namespace Faq\Controllers;

class CategoryController extends Base
{
    public const ACTION = __CLASS__;
    public const SUB_ACTIONS = [
        'add',
        'delete',
        'manage',
        'categories',
    ];

    protected function getSubActions(): array
    {
        return self::SUB_ACTIONS;
    }

    protected function getAction(): string
    {
        return self::ACTION;
    }
}