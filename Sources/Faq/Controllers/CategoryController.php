<?php

namespace Faq\Controllers;

use Faq\FaqRequest;
use Faq\Repositories\CategoryRepository;
use Faq\Services\FaqList;

class CategoryController extends BaseController
{
    public const ACTION = 'faqCategory';
    public const SUB_ACTIONS = [
        'index',
        'add',
        'delete',
        'categories',
    ];
    protected CategoryRepository $repository;

    public function __construct(
        ?FaqRequest $request = null,
        ?CategoryRepository $categoryRepository = null)
    {
        $this->repository = $categoryRepository ?? new CategoryRepository();

        parent::__construct($request);
    }

    public function index()
    {
        global $context;

        $context['sub_template'] = 'show_list';
        $context['default_list'] = 'faq_list';
        $start = $this->request->get('start', 0);

        $categoryList = new FaqList($this->repository, $start);
        $categoryList->build();
    }

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