<?php

namespace Faq\Services;

use Faq\FaqAdmin;
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
        $limit = $this->utils->setting(FaqAdmin::SETTINGS_PAGINATION, 0);
        $data = $this->faqRepository->getAll($start, $limit);
        $data['pagination'] = $this->buildPagination($start, $data['total']);

        return $data;
    }

    public function getAllCategories(int $start = 0): array
    {
        $limit = $this->utils->setting(FaqAdmin::SETTINGS_PAGINATION, 0);
        return $this->categoryRepository->getAll($start, $limit)['entities'];
    }

    public function searchBy(string $searchValue, int $start = 0): array
    {
        $limit = $this->utils->setting(FaqAdmin::SETTINGS_PAGINATION, 0);
        $data = $this->faqRepository->searchBy($searchValue, $start, $limit);
        $data['pagination'] = $this->buildPagination($start, $data['total']);

        return $data;

    }

    public function buildPagination(int $start, int $total = 0, string $paginationUrl = null): string
    {
        global $context;

        $paginationUrl = $paginationUrl ?? $context['current_url'];
        $limit = $this->utils->setting(FaqAdmin::SETTINGS_PAGINATION);

        if (empty($limit) || !$total) {
            return '';
        }

        return constructPageIndex(
            $paginationUrl,
            $start,
            $total,
            $limit
        );
    }
}