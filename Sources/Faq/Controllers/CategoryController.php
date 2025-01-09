<?php

namespace Faq\Controllers;

class CategoryController extends BaseController
{
    public const ACTION = 'faqCategory';
    public const SUB_ACTIONS = [
        'index',
        'add',
        'delete',
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

    public function getDefaultSubAction():string
    {
        return self::SUB_ACTIONS[0];
    }
}