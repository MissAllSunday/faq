<?php

namespace Faq\Services;

use Faq\FaqUtils;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\FaqRepository;

class FaqService
{
    protected CategoryRepository $categoryRepository;
    protected FaqRepository $faqRepository;
    protected FaqUtils $utils;

    public function __construct(
        CategoryRepository $categoryRepository = null,
        FaqRepository $faqRepository = null,
        FaqUtils $utils = null
    )
    {
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();
        $this->faqRepository = $faqRepository ?? new FaqRepository();
        $this->utils = $utils ?? new FaqUtils();
    }

    public function getAll(int $start = 0): array
    {
        return $this->faqRepository->getAll(true, $start);
    }

    public function getAllCategories(int $start = 0): array
    {
        return $this->categoryRepository->getAll(false, $start)['entities'];
    }
}